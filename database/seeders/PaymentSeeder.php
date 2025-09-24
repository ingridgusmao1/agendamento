<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Sale;
use App\Models\Payment;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $collectors = User::whereIn('type', ['cobrador','vendedor_cobrador'])->pluck('id')->values();
        if ($collectors->isEmpty()) {
            $this->command?->warn('Nenhum cobrador encontrado; PaymentSeeder não gerará pagamentos.');
            return;
        }

        $methods = ['à vista','cartão de crédito','crediário','outro'];

        $i = 0;
        Sale::with('installments')->orderBy('id')->chunk(100, function ($sales) use (&$i, $collectors, $methods) {
            foreach ($sales as $sale) {
                $i++;

                if ($i % 9 === 0) {
                    // pagar todas as parcelas manualmente
                    DB::transaction(function () use ($sale, $collectors, $i, $methods) {
                        foreach ($sale->installments as $ins) {
                            Payment::create([
                                'installment_id' => $ins->id,
                                'user_id'        => $collectors[($i-1) % $collectors->count()],
                                'paid_at'        => now(),
                                'amount'         => $ins->amount,
                                'note'           => 'Quitação programada',
                                'payment_method' => $methods[($i + $ins->id) % count($methods)],
                            ]);
                        }
                        // Fechar a venda
                        $sale->status = 'fechado';
                        $sale->save();
                    });

                } elseif ($i % 5 === 0 && $i % 10 !== 0) {
                    // pagamento parcial (40%) na 1ª parcela
                    $ins = $sale->installments->first();
                    if ($ins) {
                        Payment::create([
                            'installment_id' => $ins->id,
                            'user_id'        => $collectors[($i-1) % $collectors->count()],
                            'paid_at'        => now(),
                            'amount'         => round($ins->amount * 0.40, 2),
                            'note'           => 'Pagamento parcial',
                            'payment_method' => $methods[$i % count($methods)],
                        ]);
                        // status permanece 'aberto'
                    }

                } elseif ($i % 7 === 0) {
                    // sem pagamento; marca atrasado
                    $sale->status = 'atrasado';
                    $sale->save();
                }
            }
        });
    }
}
