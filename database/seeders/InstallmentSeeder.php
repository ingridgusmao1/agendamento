<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Installment, Sale};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InstallmentSeeder extends Seeder
{
    public function run(): void
    {
        $sales = Sale::query()
            ->select(['id','total','installments_qty','due_day','rescheduled_day','charge_start_date','created_at'])
            ->get();

        if ($sales->isEmpty()) {
            $this->command?->warn('Sem vendas para gerar parcelas.');
            return;
        }

        foreach ($sales as $sale) {
            $qty    = (int)($sale->installments_qty ?? 6);
            $total  = (float)$sale->total;

            $start  = $sale->charge_start_date
                ? Carbon::parse($sale->charge_start_date)->startOfDay()
                : ($sale->created_at ? Carbon::parse($sale->created_at)->startOfDay() : Carbon::today());

            $dueDay = $sale->rescheduled_day ?? $sale->due_day ?? $start->day;

            // 1º vencimento
            $firstDue = $start->copy()->day(min($dueDay, 28));
            if ($firstDue->lt($start)) {
                $firstDue->addMonthNoOverflow();
            }

            // rateio
            $base   = floor(($total / $qty) * 100) / 100;
            $values = array_fill(0, $qty, $base);
            $diff   = round($total - array_sum($values), 2);
            $values[$qty-1] = round($values[$qty-1] + $diff, 2);

            for ($n = 1; $n <= $qty; $n++) {
                $due = $firstDue->copy()->addMonthsNoOverflow($n - 1);

                Installment::create([
                    'sale_id'  => $sale->id,
                    'number'   => $n,
                    'amount'   => $values[$n-1],
                    'due_date' => $due->toDateString(),
                    'status'   => 'aberto',
                ]);
            }
        }

        // Materializa atraso (poucos, pela nova distribuição)
        $today = Carbon::today()->toDateString();
        DB::table('installments')
            ->where('status', '!=', 'pago')
            ->whereDate('due_date', '<', $today)
            ->update(['status' => 'atrasado']);
    }
}
