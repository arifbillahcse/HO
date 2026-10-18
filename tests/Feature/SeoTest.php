<?php

namespace Tests\Feature;

use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_page_names_itself_as_canonical(): void
    {
        $this->seed(ProductSeeder::class);

        foreach (['/', '/web-hosting', '/turbo-hosting', '/reseller-hosting', '/student-hosting', '/vps',
            '/business-email', '/offer', '/about-us', '/privacy-policy', '/terms-and-conditions'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertMatchesRegularExpression('#<link rel="canonical" href="([^"]+)">#', $html, $path);
            preg_match('#<link rel="canonical" href="([^"]+)">#', $html, $m);
            $this->assertSame(url($path), $m[1], "{$path}: canonical must point to the page itself");
        }
    }
}
