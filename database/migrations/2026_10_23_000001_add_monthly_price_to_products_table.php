<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Fallback monthly price; empty means the plan is sold yearly only. */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('monthly_price', 10, 2)->nullable()->after('yearly_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('monthly_price');
        });
    }
};
