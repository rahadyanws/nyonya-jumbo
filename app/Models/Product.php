<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    // Casting agar 'is_available' dibaca boolean oleh Laravel
    protected $casts = [
        'is_available' => 'boolean',
    ];
}