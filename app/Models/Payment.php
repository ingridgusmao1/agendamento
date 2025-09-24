<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'installment_id','user_id','paid_at','amount','note','payment_method'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'float',
    ];

    public function installment()
    {
        return $this->belongsTo(Installment::class, 'installment_id', 'id');
    }

    // (opcional) “atalho” para chegar na venda a partir do payment.
    public function sale()
    {
        return $this->hasOneThrough(
            Sale::class,
            Installment::class,
            'id',      // Installments.id
            'id',      // Sales.id
            'installment_id', // Payments.installment_id -> Installments.id
            'sale_id'         // Installments.sale_id -> Sales.id
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
