<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_key', 60)->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('name', 100);
            $table->string('seo_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('focus_keyword', 100)->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->string('og_title')->nullable();
            $table->string('og_description', 500)->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();
        });

        $pages = [
            'home' => 'Home',
            'web_hosting' => 'Web Hosting',
            'turbo_hosting' => 'Turbo Hosting',
            'reseller_hosting' => 'Reseller Hosting',
            'student_hosting' => 'Student Hosting',
            'vps' => 'VPS',
            'business_email' => 'Business Email',
            'offer' => 'Offer',
            'about_us' => 'About Us',
            'privacy_policy' => 'Privacy Policy',
            'terms_and_conditions' => 'Terms and Conditions',
        ];

        $order = 0;
        foreach ($pages as $key => $name) {
            DB::table('seo_pages')->insert([
                'page_key' => $key,
                'sort_order' => ++$order,
                'name' => $name,
                'meta_description' => $key === 'terms_and_conditions'
                    ? 'Read the Terms and Conditions for Hostorio web hosting services. Understand our refund policy, cancellation policy, domain policy, and more.'
                    : null,
                'robots_index' => true,
                'robots_follow' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_pages');
    }
};
