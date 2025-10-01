<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'aberto';
    public const STATUS_CLOSED = 'fechado';
    public const STATUS_DELAYED = 'atrasado';

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

    protected static function booted()
    {
        static::creating(function ($sale) {
            if (empty($sale->number)) {
                $lastId = Sale::max('id') + 1;
                $sale->number = 'N'.str_pad($lastId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

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
        return $this->hasMany(Installment::class, 'sale_id', 'id');
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function payments()
    {
        return $this->hasManyThrough(
            Payment::class,      // related
            Installment::class,  // through
            'sale_id',       // Foreign key on installments referencing sales
            'installment_id',// Foreign key on payments referencing installments
            'id',            // Local key on sales
            'id'             // Local key on installments
        );
    }

    public function remainingBalance(): float
    {
        $sumInstallments = (float) $this->installments()->sum('amount');
        $paymentSum = (float) Payment::whereIn('installment_id', $this->installments()->pluck('id'))->sum('amount');
        return $sumInstallments - $paymentSum;
    }
}
