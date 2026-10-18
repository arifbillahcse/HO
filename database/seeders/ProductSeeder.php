<?php

namespace Database\Seeders;

use App\Models\ComparisonRow;
use App\Models\Product;
use App\Services\WhmcsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Imports the plan cards and comparison tables that the pages showed before
 * Products & Services existed. Runs only on an empty table, so it never
 * overwrites changes made in the admin.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::query()->exists() || ComparisonRow::query()->exists()) {
            $this->command?->warn('Products already exist, import skipped.');

            return;
        }

        $data = json_decode(file_get_contents(database_path('data/products.json')), true, flags: JSON_THROW_ON_ERROR);
        $whmcs = app(WhmcsService::class);

        DB::transaction(function () use ($data, $whmcs) {
            foreach ($data as $page => $info) {
                $rowIds = [];
                foreach ($info['comparison_rows'] as $i => $label) {
                    $rowIds[] = ComparisonRow::create(['page_key' => $page, 'sort_order' => $i + 1, 'label' => $label])->id;
                }

                foreach ($info['products'] as $slot => $product) {
                    $comparison = [];
                    foreach ($product['comparison'] as $i => $value) {
                        if (isset($rowIds[$i])) {
                            $comparison[(string) $rowIds[$i]] = $value;
                        }
                    }

                    Product::create([
                        'page_key' => $page,
                        'sort_order' => $slot + 1,
                        'is_visible' => true,
                        'name' => $product['name'],
                        'description' => $product['description'],
                        'is_popular' => $product['is_popular'],
                        'badge_text' => $product['badge_text'],
                        'icon_alt' => $product['icon_alt'],
                        'features' => $product['features'],
                        'show_original_price' => $product['show_original_price'],
                        'button_text' => $product['button_text'] ?: 'Order Now',
                        'whmcs_pid' => isset(WhmcsService::PLANS[$page]) ? $whmcs->planPid($page, $slot) : null,
                        'yearly_price' => $product['yearly_price'],
                        'order_url' => $product['order_url'],
                        'comparison' => $comparison,
                    ]);
                }
            }
        });

        $this->command?->info('Imported ' . Product::count() . ' products and ' . ComparisonRow::count() . ' comparison rows.');
    }
}
