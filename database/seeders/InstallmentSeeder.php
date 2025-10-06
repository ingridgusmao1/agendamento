<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Installment, Sale};
use Carbon\Carbon;

class InstallmentSeeder extends Seeder
{
    public function run(): void
    {
        $sales = Sale::all();
        if ($sales->isEmpty()) return;

        // NÃO use truncate aqui por causa de FKs (payments -> installments)
        Installment::query()->delete();

        $today = Carbon::today();

        foreach ($sales as $sale) {
            // mantém a faixa de 6..12 como no seu padrão
            $qty = rand(6, 12);
            // distribui o valor por parcela de forma simples
            $baseAmount = round($sale->total / $qty, 2);

            for ($n = 1; $n <= $qty; $n++) {
                // distribuição de vencimentos: menos atrasados, mais no dia/+3/+5 e alguns futuros
                // (ajuste fino conforme desejar)
                $daysOffset = collect([
                    -7, -2,                  // alguns atrasados
                    0, 0,                    // do dia (mais frequência)
                    1, 2, 3,                 // em até 3 dias
                    4, 5,                    // em até 5 dias
                    8, 12, 15                // futuros
                ])->random();

                $dueDate = $today->copy()->addDays($daysOffset);

                // status coerente com o vencimento:
                // - se já passou e não pagou: 'atrasado'
                // - se não passou: 'aberto'
                // - alguns poucos já nascem 'pago' para teste
                // - raros 'parcial' para simular pagamento parcial
                $roll = rand(1, 100);

                if ($daysOffset < 0) {
                    // vencido
                    if ($roll <= 15) {
                        $status = 'pago';      // alguns pagos mesmo vencidos (pagou ontem, por ex.)
                    } elseif ($roll <= 20) {
                        $status = 'parcial';   // poucos parciais
                    } else {
                        $status = 'atrasado';  // maioria atrasada
                    }
                } else {
                    // ainda não venceu ou vence hoje
                    if ($daysOffset === 0 && $roll <= 10) {
                        $status = 'pago';      // alguns pagos "do dia"
                    } else {
                        $status = 'aberto';    // padrão
                    }
                }

                Installment::create([
                    'sale_id'  => $sale->id,
                    'number'   => $n,
                    'amount'   => $baseAmount,
                    'due_date' => $dueDate,
                    'status'   => $status, // << usar apenas: aberto | parcial | pago | atrasado
                ]);
            }
        }
    }
}
