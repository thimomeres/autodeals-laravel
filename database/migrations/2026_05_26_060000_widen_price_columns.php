<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE cars MODIFY price DECIMAL(18, 2) NOT NULL');
        DB::statement('ALTER TABLE offers MODIFY price_offered DECIMAL(18, 2) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE cars MODIFY price DECIMAL(15, 2) NOT NULL');
        DB::statement('ALTER TABLE offers MODIFY price_offered DECIMAL(15, 2) NOT NULL');
    }
};
