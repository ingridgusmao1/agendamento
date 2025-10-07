<?php

namespace App\Http\Services;
use App\Models\{Installment,Payment};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentService {
    public static function pay(Installment $ins, float $amount, int $userId, ?string $note = null, ?string $paymentMethod = null): Installment
    {
        Payment::create([
            'installment_id' => $ins->id,
            'user_id'        => $userId,
            'paid_at'        => Carbon::now(),
            'amount'         => $amount,
            'note'           => $note,
            'payment_method' => $paymentMethod, // <-- NOVO
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

    public function createPaymentForInstallment(Installment $installment, int $userId, float $amount, string $method, ?string $note): void
    {
        DB::transaction(function () use ($installment, $userId, $amount, $method, $note) {
            $inst = Installment::whereKey($installment->id)->lockForUpdate()->first();

            $remaining = max(0, (float)$inst->amount - (float)$inst->paid_total);
            if ($amount > $remaining + 0.00001) {
                throw new \RuntimeException('Pagamento acima do restante.');
            }

            Payment::create([
                'installment_id' => $inst->id,
                'user_id'        => $userId,
                'amount'         => $amount,
                'payment_method' => $method,
                'note'           => $note,
                'paid_at'        => now(),
            ]);

            $inst->paid_total = (float)$inst->paid_total + $amount;
            if (abs((float)$inst->paid_total - (float)$inst->amount) < 0.00001) {
                $inst->status = 'pago';
            }
            $inst->save();
        });
    }
}