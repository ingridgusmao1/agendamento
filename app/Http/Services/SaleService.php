<?php

namespace App\Http\Services;

use App\Models\{Sale, Installment};
use Carbon\Carbon;

class SaleService {
    public static function createInstallments(Sale $sale): void
    {
        $base  = Carbon::parse($sale->charge_start_date)->day($sale->due_day);
        $value = round($sale->total / $sale->installments_qty, 2);

        for ($i=1; $i <= $sale->installments_qty; $i++) {
            $due = (clone $base)->addMonthsNoOverflow($i-1);
            if ($sale->rescheduled_day) $due->day($sale->rescheduled_day);

            Installment::create([
                'sale_id'=>$sale->id,
                'number'=>$i,
                'due_date'=>$due,
                'amount'=>$value,
            ]);
        }
    }
}
