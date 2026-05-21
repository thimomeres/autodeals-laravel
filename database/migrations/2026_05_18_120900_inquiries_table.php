<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('inquiries', function (Blueprint $table) {
        $table->id();
        // Menghubungkan data penawaran ke id mobil
        $table->foreignId('car_id')->constrained('cars')->onDelete('cascade');
        $table->string('buyer_name');
        $table->string('buyer_email');
        $table->decimal('offered_price', 15, 2);
        $table->string('status')->default('pending'); // pending, approved, rejected
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
