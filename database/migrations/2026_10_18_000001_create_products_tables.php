<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('page_key', 40)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->string('name');
            $table->string('description', 500)->nullable();
            $table->boolean('is_popular')->default(false);
            $table->string('badge_text', 60)->nullable();
            $table->string('icon')->nullable();
            $table->string('icon_alt')->nullable();
            $table->json('features')->nullable();
            $table->boolean('show_original_price')->default(true);
            $table->string('button_text', 60)->default('Order Now');
            $table->unsignedInteger('whmcs_pid')->nullable();
            $table->decimal('yearly_price', 12, 2)->nullable();
            $table->string('order_url')->nullable();
            $table->json('comparison')->nullable();
            $table->timestamps();
        });

        Schema::create('comparison_rows', function (Blueprint $table) {
            $table->id();
            $table->string('page_key', 40)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('label');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comparison_rows');
        Schema::dropIfExists('products');
    }
};
