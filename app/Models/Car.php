<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    use HasFactory;

    protected $table = 'cars'; 
    protected $guarded = ['id'];

    // Relasi: Satu mobil punya banyak gambar
    public function images(): HasMany
    {
        return $this->hasMany(CarImage::class, 'car_id');
    }

    // ✨ Sinkronisasi Relasi ke Tabel Offers
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'car_id');
    }

    // Tetap pertahankan ini jika Anda juga memiliki tabel inquiry terpisah
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'car_id');
    }
}