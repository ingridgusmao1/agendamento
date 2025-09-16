<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'customer_id',
        'seller_id',
        'total',
        'installments_qty',
        'due_day',
        'rescheduled_day',
        'charge_start_date',
        'delivery_text',
        'gps_lat',
        'gps_lng',
        'collection_note',
        'status',
    ];

    protected $casts = [
        'total'              => 'float',
        'gps_lat'            => 'float',
        'gps_lng'            => 'float',
        'charge_start_date'  => 'date',
        'installments_qty'   => 'integer',
        'due_day'            => 'integer',
        'rescheduled_day'    => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function installments()
    {
        return $this->hasMany(Installment::class)->orderBy('number');
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function remainingBalance(): float
    {
        $sumInstallments = (float) $this->installments()->sum('amount');
        $paymentSum = (float) Payment::whereIn('installment_id', $this->installments()->pluck('id'))->sum('amount');
        return $sumInstallments - $paymentSum;
    }
}
