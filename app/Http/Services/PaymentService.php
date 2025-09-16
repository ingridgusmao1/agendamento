<?php

namespace App\Http\Services;
use App\Models\{Installment,Payment};
use Carbon\Carbon;

class PaymentService {
    public static function pay(Installment $ins, float $amount, int $userId, ?string $note=null): Installment
    {
        Payment::create([
            'installment_id'=>$ins->id,
            'user_id'=>$userId,
            'paid_at'=>Carbon::now(),
            'amount'=>$amount,
            'note'=>$note,
        ]);

        $paid = $ins->payments()->sum('amount');
        $ins->paid_total = $paid;

        if ($paid <= 0) {
            $ins->status = 'aberto';
        } elseif ($paid < $ins->amount) {
            $ins->status = 'parcial';
        } else {
            $ins->status = 'pago';
        }

        $ins->save();
        return $ins;
    }
}