<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','model','color','size','price','notes','complements','photo_path',
    ];

    protected $casts = [
        'price'       => 'float',
        'complements' => 'array', // JSON
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
