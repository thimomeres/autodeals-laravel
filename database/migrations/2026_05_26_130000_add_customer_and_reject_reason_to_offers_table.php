<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->after('car_id')
                ->constrained('customers')
                ->nullOnDelete();

            $table->string('reject_reason', 1000)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
            $table->dropColumn('reject_reason');
        });
    }
};
