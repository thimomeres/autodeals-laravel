<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('offers', function (Blueprint $table) {
        $table->id();
        // Menghubungkan penawaran dengan ID mobil di tabel cars
        $table->foreignId('car_id')->constrained('cars')->onDelete('cascade');
        $table->string('buyer_name');
        $table->decimal('price_offered', 15, 2);
        // Status awal otomatis 'pending_review'
        $table->enum('status', ['pending_review', 'approved', 'rejected'])->default('pending_review');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
