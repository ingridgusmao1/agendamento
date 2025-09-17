<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id','number','due_date','amount','paid_total','status',
    ];

    protected $casts = [
        'number'    => 'integer',
        'due_date'  => 'date',
        'amount'    => 'float',
        'paid_total'=> 'float',
    ];

    public function sale(){ return $this->belongsTo(Sale::class); }
    public function payments(){ return $this->hasMany(Payment::class); }
}
