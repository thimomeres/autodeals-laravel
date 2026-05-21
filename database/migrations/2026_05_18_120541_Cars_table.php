<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
{
    Schema::create('cars', function (Blueprint $table) {
        $table->id();
        $table->string('stock_code')->unique();
        $table->string('brand');
        $table->string('model');
        $table->integer('year');
        $table->decimal('price', 15, 2); // Menggunakan decimal agar aman untuk nominal besar Rupiah
        $table->integer('mileage');
        $table->string('color')->comment('Warna');
        $table->string('transmission');
        $table->string('fuel_type');
        $table->integer('engine_capacity_cc') ->comment('Kapasitas Mechine');
        $table->string('plate_number')->nullable();
        $table->string('condition');
        $table->integer('fuel_tank_capacity')->comment('Kapasitas tangki dalam liter');
        $table->integer('seating_capacity')->comment('Jumlah kapasitas kursi');
        $table->string('vin_number')->nullable();
        $table->text('description')->nullable();
        $table->string('status')->default('available'); // available, reserved, sold
        
        // Foreign Key ke tabel users (siapa yang input data)
        $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        $table->timestamps();
    });
}
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
