<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name','model','color','size','price','notes','complements','photo_path',
    ];

    protected $casts = [
        'price'       => 'float',
        'complements' => 'array', // JSON
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'product_id');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'product_id');
    }
}
