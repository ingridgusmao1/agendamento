<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','street','number','district','city','reference_point',
        'rg','cpf','phone','other_contact','lat','lng','avatar_path','place_path'
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'customer_id');
    }
}
