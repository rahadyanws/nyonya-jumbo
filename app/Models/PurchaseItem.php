<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi balik ke Nota (Parent)
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    // Relasi ke Bahan Baku
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}