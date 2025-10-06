<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Payment, Installment, Sale, User};
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $installments = Installment::where('status', 'pago')->get();
        if ($installments->isEmpty()) return;

        Payment::query()->delete();
        $notes = [
            'Cliente pagou via PIX.',
            'Pagamento em dinheiro no balcão.',
            'Cliente alegou atraso no depósito.',
            'Pagou com cartão de crédito.',
            'Pagou parcialmente e prometeu quitar amanhã.',
            'Pagamento antecipado com desconto.',
            'Cliente enviou comprovante pelo WhatsApp.',
        ];

        foreach ($installments as $inst) {
            $sale = Sale::find($inst->sale_id);
            if (!$sale) continue;

            $user = User::find($sale->seller_id);
            $paidAt = Carbon::parse($inst->due_date)->copy()->subDays(rand(0, 3));

            Payment::create([
                'installment_id' => $inst->id,
                'user_id'        => $user?->id,
                'amount'         => $inst->amount,
                'paid_at'        => $paidAt,
                'payment_method' => collect(['dinheiro','pix','credito','debito'])->random(),
                'note'           => collect($notes)->random(),
            ]);
        }
    }
}
