<?php

namespace Tests\Feature;

use App\Filament\Resources\ComparisonRows\Pages\ManageComparisonRows;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\ComparisonRow;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductsAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
    }

    public function test_import_matches_what_the_pages_showed(): void
    {
        $this->assertSame(34, Product::count());
        $this->assertSame(145, ComparisonRow::count());

        $counts = Product::query()->selectRaw('page_key, count(*) as n')->groupBy('page_key')->pluck('n', 'page_key')->map(fn ($n) => (int) $n)->all();
        $this->assertEquals(['home' => 4, 'web_hosting' => 4, 'turbo_hosting' => 4, 'reseller_hosting' => 4, 'student_hosting' => 3, 'vps' => 4, 'business_email' => 3, 'offer' => 4, 'package' => 4], $counts);

        $starter = Product::onPage('home')->first();
        $this->assertSame('Starter', $starter->name);
        $this->assertSame(['value' => '5GB', 'text' => 'NVMe Storage', 'is_new' => false], $starter->features[0]);
        $this->assertCount(11, $starter->features);
        $this->assertSame(55, $starter->whmcs_pid);
        $this->assertSame('3000.00', $starter->yearly_price);
        $this->assertNull($starter->order_url);

        $turboPower = Product::onPage('turbo_hosting')->skip(1)->first();
        $this->assertTrue($turboPower->is_popular);
        $this->assertSame('Recommended', $turboPower->badge_text);

        $this->assertSame('DEPLOY NOW', Product::onPage('vps')->first()->button_text);
        $this->assertSame('Get Started', Product::onPage('business_email')->first()->button_text);

        $offer = Product::onPage('offer')->first();
        $this->assertFalse($offer->show_original_price);
        $this->assertSame('https://my.hostorio.com/cart.php?a=add&pid=16&sld=whmcs&tld=.com', $offer->order_url);
        $this->assertSame(16, $offer->whmcs_pid);

        $this->assertNull(Product::onPage('package')->first()->whmcs_pid);

        $rows = ComparisonRow::onPage('web_hosting')->get();
        $this->assertCount(27, $rows);
        $this->assertSame('Storage Space', $rows->first()->label);
        $webStarter = Product::onPage('web_hosting')->first();
        $this->assertSame(['type' => 'text', 'text' => '5GB NVMe'], $webStarter->comparison[(string) $rows->first()->id]);
        $this->assertCount(27, $webStarter->comparison);
    }

    public function test_import_never_runs_twice(): void
    {
        Product::onPage('home')->first()->update(['name' => 'Edited']);

        $this->seed(ProductSeeder::class);

        $this->assertSame(34, Product::count());
        $this->assertSame('Edited', Product::onPage('home')->first()->name);
    }

    public function test_admin_screens_load(): void
    {
        $this->get('/admin/products')->assertOk()->assertSee('Turbo Power')->assertSee('Web Hosting');
        $this->get('/admin/comparison-rows')->assertOk()->assertSee('Storage Space');

        $product = Product::onPage('web_hosting')->first();
        $this->get("/admin/products/{$product->id}/edit")->assertOk()->assertSee('Storage Space');
    }

    public function test_editing_a_product_saves_card_specs_and_comparison(): void
    {
        $product = Product::onPage('web_hosting')->first();
        $row = ComparisonRow::onPage('web_hosting')->first();
        $otherPageRow = ComparisonRow::onPage('turbo_hosting')->first();

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm([
                'name' => 'Starter Plus',
                'features' => [
                    ['value' => '10GB', 'text' => 'NVMe Storage', 'is_new' => true],
                    ['value' => null, 'text' => 'Free SSL Certificate', 'is_new' => false],
                ],
                "comparison.{$row->id}.type" => 'yes',
                "comparison.{$otherPageRow->id}.type" => 'text',
                "comparison.{$otherPageRow->id}.text" => 'should be dropped',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();
        $this->assertSame('Starter Plus', $product->name);
        $this->assertSame('10GB', $product->features[0]['value']);
        $this->assertTrue($product->features[0]['is_new']);
        $this->assertCount(2, $product->features);
        $this->assertSame(['type' => 'yes'], $product->comparison[(string) $row->id]);
        $this->assertArrayNotHasKey((string) $otherPageRow->id, $product->comparison);
    }

    public function test_badge_text_is_required_only_for_highlighted_plans(): void
    {
        $product = Product::onPage('home')->first();

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm(['is_popular' => true, 'badge_text' => ''])
            ->call('save')
            ->assertHasFormErrors(['badge_text' => 'required']);
    }

    public function test_creating_a_product_appends_it_to_the_page(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm([
                'page_key' => 'vps',
                'name' => 'Mega VPS',
                'button_text' => 'DEPLOY NOW',
                'yearly_price' => 200000,
                'features' => [['value' => '32 Cores', 'text' => 'CPU', 'is_new' => false]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $created = Product::where('name', 'Mega VPS')->firstOrFail();
        $this->assertSame('vps', $created->page_key);
        $this->assertSame(5, $created->sort_order);
        $this->assertTrue($created->is_visible);
        $this->assertSame([], $created->comparison);
    }

    public function test_duplicate_creates_a_hidden_copy_at_the_end_of_the_page(): void
    {
        $original = Product::onPage('home')->first();

        Livewire::test(ListProducts::class)
            ->callAction(TestAction::make('replicate')->table($original));

        $copy = Product::where('name', 'Starter (copy)')->where('page_key', 'home')->firstOrFail();
        $this->assertFalse($copy->is_visible);
        $this->assertSame(5, $copy->sort_order);
        $this->assertEquals($original->features, $copy->features);
    }

    public function test_visibility_toggle_and_comparison_row_management(): void
    {
        $product = Product::onPage('home')->first();

        Livewire::test(ListProducts::class)
            ->call('updateTableColumnState', 'is_visible', (string) $product->getKey(), false);
        $this->assertFalse($product->refresh()->is_visible);

        Livewire::test(ManageComparisonRows::class)
            ->callAction('create', ['page_key' => 'web_hosting', 'label' => 'Malware Scanner'])
            ->assertHasNoActionErrors();

        $row = ComparisonRow::where('label', 'Malware Scanner')->firstOrFail();
        $this->assertSame(28, $row->sort_order);
    }
}
