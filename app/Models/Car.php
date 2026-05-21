<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    use HasFactory;

    // Memastikan Laravel menembak tabel 'cars' di MySQL
    protected $table = 'cars'; 

    // Mengizinkan semua kolom diisi secara massal (kecuali ID)
    // SANGAT COCOK karena kolom Anda banyak, jadi tidak perlu ngetik satu-persatu lagi.
    protected $guarded = ['id'];

    // Relasi: Satu mobil punya banyak gambar
    public function images(): HasMany
    {
        return $this->hasMany(CarImage::class, 'car_id');
    }

    // Relasi: Satu mobil punya banyak penawaran (inquiries)
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'car_id');
    }
}