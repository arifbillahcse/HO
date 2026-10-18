<?php

namespace Database\Seeders;

use App\Models\ComparisonRow;
use App\Models\Product;
use App\Services\SettingsService;
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
        // Product IDs saved on the WHMCS Connection page before products existed.
        $savedPids = SettingsService::get('whmcs_plan_pids');
        $savedPids = is_array($savedPids) ? $savedPids : [];

        DB::transaction(function () use ($data, $savedPids) {
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
                        'whmcs_pid' => array_key_exists("{$page}.{$slot}", $savedPids)
                            ? (filled($savedPids["{$page}.{$slot}"]) ? (int) $savedPids["{$page}.{$slot}"] : null)
                            : $product['whmcs_pid'],
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
