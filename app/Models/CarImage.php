<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarImage extends Model
{
    protected $guarded = ['id'];
    
    // Matikan timestamps jika di tabel car_images Anda tidak membuat $table->timestamps() lengkap
    public $timestamps = false; 
}