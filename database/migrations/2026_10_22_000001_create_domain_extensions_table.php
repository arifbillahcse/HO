<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_extensions', function (Blueprint $table) {
            $table->id();
            $table->string('tld', 63)->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->string('badge', 10)->nullable(); // null = WHMCS group; none / hot / new / sale
            $table->timestamps();
        });

        // The new Domain Registration page gets its own Page SEO entry.
        if (Schema::hasTable('seo_pages') && ! DB::table('seo_pages')->where('page_key', 'domain_registration')->exists()) {
            DB::table('seo_pages')->insert([
                'page_key' => 'domain_registration',
                'sort_order' => (int) DB::table('seo_pages')->max('sort_order') + 1,
                'name' => 'Domain Registration',
                'seo_title' => 'Domain Registration in Bangladesh %sep% Hostorio',
                'meta_description' => 'Domain registration in Bangladesh at live prices: .com, .com.bd, .net, .xyz and more. Pay with bKash, Nagad or card, with 24/7 local support.',
                'focus_keyword' => 'domain registration',
                'robots_index' => true,
                'robots_follow' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_extensions');
        DB::table('seo_pages')->where('page_key', 'domain_registration')->delete();
    }
};
