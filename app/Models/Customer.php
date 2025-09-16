<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','street','number','district','city','reference_point',
        'rg','cpf','phone','other_contact','lat','lng',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
