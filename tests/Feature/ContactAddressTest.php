<?php

namespace Tests\Feature;

use App\Filament\Pages\ContactAddress;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingsService;
use Database\Seeders\ProductSeeder;
use Database\Seeders\SettingSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactAddressTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        '/', '/web-hosting', '/turbo-hosting', '/reseller-hosting', '/student-hosting', '/vps',
        '/business-email', '/offer', '/about-us', '/privacy-policy', '/terms-and-conditions',
    ];

    private const NEW_DETAILS = [
        'business_name' => 'Hostorio Ltd',
        'business_phone' => '+880 1711-000000',
        'business_email' => 'hello@example.com',
        'client_area_url' => 'https://billing.example.com',
        'business_address' => 'House 5, Road 7',
        'business_city' => 'Dhanmondi',
        'business_region' => 'Dhaka',
        'business_latitude' => '23.7461',
        'business_longitude' => '90.3742',
        'social_facebook' => 'https://www.facebook.com/example',
        'social_twitter' => '',
        'social_youtube' => '',
        'social_linkedin' => 'https://www.linkedin.com/company/example/',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SettingSeeder::class, ProductSeeder::class]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
    }

    private function saveInAdmin(array $details = self::NEW_DETAILS): void
    {
        Livewire::test(ContactAddress::class)
            ->fillForm($details)
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Saved');
    }

    public function test_admin_page_shows_current_details(): void
    {
        $this->get('/admin/contact-address')->assertOk()
            ->assertSee('Contact &amp; Address', false)
            ->assertSee('+8801779440297')
            ->assertSee('Kanchan, Rupganj')
            ->assertSee('Narayanganj');
    }

    public function test_new_details_replace_the_old_ones_on_every_page(): void
    {
        $this->saveInAdmin();

        foreach (self::PAGES as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            foreach (['8801779440297', 'mail@hostorio.com', 'OABDA', 'Rupganj', 'x.com/hostorio', 'youtube.com/@arifbillahcse',
                'my.hostorio.com/submitticket', 'my.hostorio.com/knowledgebase', 'my.hostorio.com/cart.php?a=add&amp;domain='] as $old) {
                $this->assertStringNotContainsString($old, $html, "{$url} still shows {$old}");
            }

            $this->assertStringContainsString('+880 1711-000000', $html, $url);
            $this->assertStringContainsString('href="tel:+8801711000000"', $html, $url);
            $this->assertStringContainsString('<span>Dhanmondi, Dhaka</span>', $html, $url);
            $this->assertStringContainsString('<span>House 5, Road 7</span>', $html, $url);
            $this->assertStringContainsString('<span>hello@example.com</span>', $html, $url);
            $this->assertStringContainsString('href="https://billing.example.com/supporttickets.php"', $html, $url);
            $this->assertSame(2, substr_count($html, 'class="social-icon"'), "{$url}: empty social links are hidden");
        }

        $this->get('/about-us')->assertSee('href="https://billing.example.com/submitticket.php"', false)->assertSee('Dhanmondi, Dhaka');
        $this->get('/terms-and-conditions')->assertSee('<a href="mailto:hello@example.com">hello@example.com</a>', false);
        $this->get('/')->assertSee('action="https://billing.example.com/cart.php?a=add&domain=register"', false);
    }

    public function test_structured_data_uses_the_new_details(): void
    {
        $this->saveInAdmin();

        $organization = $this->schema('/', 'Organization');
        $this->assertSame('Hostorio Ltd', $organization['name']);
        $this->assertSame('+880 1711-000000', $organization['contactPoint']['telephone']);
        $this->assertSame('hello@example.com', $organization['contactPoint']['email']);
        $this->assertSame(['@type' => 'PostalAddress', 'streetAddress' => 'House 5, Road 7', 'addressLocality' => 'Dhanmondi', 'addressRegion' => 'Dhaka', 'addressCountry' => 'BD'], $organization['address']);
        $this->assertSame(['https://www.facebook.com/example', 'https://www.linkedin.com/company/example/'], $organization['sameAs']);

        $local = $this->schema('/web-hosting', 'LocalBusiness');
        $this->assertSame(['@type' => 'GeoCoordinates', 'latitude' => 23.7461, 'longitude' => 90.3742], $local['geo']);

        $this->saveInAdmin(['business_latitude' => '', 'business_longitude' => ''] + self::NEW_DETAILS);
        $this->assertArrayNotHasKey('geo', $this->schema('/web-hosting', 'LocalBusiness'));

        $this->assertSame('Hostorio Ltd', $this->schema('/about-us', 'AboutPage')['mainEntity']['name']);
    }

    public function test_admin_page_validates_and_normalises_input(): void
    {
        Livewire::test(ContactAddress::class)
            ->fillForm(['business_email' => 'not-an-email', 'client_area_url' => 'http://billing.example.com', 'business_phone' => '', 'social_facebook' => 'facebook'])
            ->call('save')
            ->assertHasFormErrors(['business_email', 'client_area_url', 'business_phone', 'social_facebook']);

        $this->saveInAdmin(['client_area_url' => 'https://billing.example.com///'] + self::NEW_DETAILS);
        $this->assertSame('https://billing.example.com/', SettingsService::get('client_area_url'));
    }

    public function test_contact_keys_are_hidden_from_generic_settings(): void
    {
        $this->get('/admin/settings')->assertOk()->assertDontSee('business_phone')->assertSee('exchange_rate');
    }

    public function test_seeding_again_keeps_admin_edits(): void
    {
        $this->saveInAdmin();
        $this->seed(SettingSeeder::class);

        $this->assertSame('+880 1711-000000', SettingsService::get('business_phone'));
    }

    public function test_old_combined_city_is_split_into_city_and_district(): void
    {
        Setting::where('key', 'business_region')->delete();
        Setting::set('business_city', 'Kanchan, Rupganj, Narayanganj');

        (require database_path('migrations/2026_10_19_000001_split_business_city_setting.php'))->up();

        $this->assertSame('Kanchan, Rupganj', SettingsService::get('business_city'));
        $this->assertSame('Narayanganj', SettingsService::get('business_region'));
        $this->get('/')->assertSee('<span>Kanchan, Rupganj, Narayanganj</span>', false);
    }

    private function schema(string $url, string $type): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $this->get($url)->getContent(), $m);

        foreach ($m[1] as $json) {
            $data = json_decode($json, true);
            $this->assertIsArray($data, "{$url}: invalid JSON-LD");
            if (($data['@type'] ?? null) === $type) {
                return $data;
            }
        }

        $this->fail("{$url}: no {$type} schema");
    }
}
