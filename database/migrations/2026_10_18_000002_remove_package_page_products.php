<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')->where('page_key', 'package')->delete();
        DB::table('comparison_rows')->where('page_key', 'package')->delete();
    }

    public function down(): void
    {
    }
};
