<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Installment, Payment};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $installments = Installment::with('sale')->get();
        if ($installments->isEmpty()) {
            $this->command?->warn('Sem parcelas para gerar pagamentos.');
            return;
        }

        $methods = ['dinheiro','pix','debito','credito'];
        $today   = Carbon::today()->startOfDay();

        $notesPaid = [
            'Pagamento integral realizado sem pendências.',
            'Quitou o valor integral conforme combinado.',
            'Cliente antecipou e pagou o total.',
        ];

        $notesPartialFuture = [
            'Pagou parte hoje e combinou o restante para semana que vem.',
            'Cliente alegou gasto inesperado; promete completar no próximo pagamento.',
            'Pagou metade e pediu prazo até sexta para quitar.',
        ];

        $notesPartialOverdue = [
            'Cliente atrasou por problema de saúde; disse que regulariza até sexta.',
            'Atrasou por falta de recebimento; promete completar no próximo depósito.',
            'Relatou imprevisto no trabalho; vai pagar o restante em dois dias.',
        ];

        foreach ($installments as $inst) {
            $sale = $inst->sale;
            if (!$sale) continue;

            $roll  = mt_rand(1, 100); // distribuição
            $due   = Carbon::parse($inst->due_date)->startOfDay();
            $start = $sale->created_at ? Carbon::parse($sale->created_at)->startOfDay() : $today->copy();

            // candidato entre 2 dias antes e 7 depois do vencimento
            $candidate = $due->copy()->addDays(mt_rand(-2, 7));

            // clamp: nunca antes da venda; nunca no futuro
            if ($candidate->lt($start))  $candidate = $start->copy()->addHours(mt_rand(9, 18));
            if ($candidate->gt($today))  $candidate = $today->copy()->addHours(mt_rand(9, 18));

            $paidAt   = $candidate;
            $method   = $methods[array_rand($methods)];
            $sellerId = $sale->seller_id;

            if ($roll <= 35) {
                // pago integral
                $note = $notesPaid[array_rand($notesPaid)];
                DB::transaction(function () use ($inst, $sellerId, $method, $paidAt, $note) {
                    Payment::create([
                        'installment_id' => $inst->id,
                        'user_id'        => $sellerId,
                        'amount'         => $inst->amount,
                        'paid_at'        => $paidAt,
                        'payment_method' => $method,
                        'note'           => $note,
                        'created_at'     => $paidAt,
                        'updated_at'     => $paidAt,
                    ]);
                    $inst->update(['status' => 'pago']);
                });
            } elseif ($roll <= 55) {
                // parcial (30%..80%)
                $partial = max(1, round($inst->amount * (mt_rand(30, 80) / 100), 2));
                $isOverdueAfterPartial = $due->lt($today);
                $note = $isOverdueAfterPartial
                    ? $notesPartialOverdue[array_rand($notesPartialOverdue)]
                    : $notesPartialFuture[array_rand($notesPartialFuture)];

                DB::transaction(function () use ($inst, $sellerId, $method, $partial, $paidAt, $today, $isOverdueAfterPartial, $note) {
                    Payment::create([
                        'installment_id' => $inst->id,
                        'user_id'        => $sellerId,
                        'amount'         => $partial,
                        'paid_at'        => $paidAt,
                        'payment_method' => $method,
                        'note'           => $note,
                        'created_at'     => $paidAt,
                        'updated_at'     => $paidAt,
                    ]);
                    $inst->update(['status' => $isOverdueAfterPartial ? 'atrasado' : 'parcial']);
                });
            } else {
                // sem pagamento: mantém 'aberto' ou 'atrasado' que veio do InstallmentSeeder
                continue;
            }
        }
    }
}
