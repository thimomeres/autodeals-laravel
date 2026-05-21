<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    // Menentukan kolom mana saja yang boleh diisi (Mass Assignment) oleh Postman/Form
    protected $fillable = ['car_id', 'buyer_name', 'price_offered', 'status'];

    /**
     * Relasi balik ke model Car.
     * Sebuah penawaran (Offer) ditujukan untuk satu mobil (Car).
     */
    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}