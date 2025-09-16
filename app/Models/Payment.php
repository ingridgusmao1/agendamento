<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'installment_id','user_id','paid_at','amount','note',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'float',
    ];

    public function installment(){ return $this->belongsTo(Installment::class); }
    public function user(){ return $this->belongsTo(User::class); }
}
