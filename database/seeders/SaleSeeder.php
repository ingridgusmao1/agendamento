<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{User, Customer, Product, Sale};
use App\Http\Services\SaleService;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $sellers   = User::whereIn('type', ['vendedor','vendedor_cobrador'])->pluck('id')->values();
        $customers = Customer::orderBy('id')->pluck('id')->values();
        $products  = Product::orderBy('id')->get();

        $obs = [
            'Cobrar no posto de gasolina',
            'Entregar na casa da sogra',
            'Cliente trabalha à tarde, cobrar de manhã',
            'Evitar horário de almoço',
            'Casa sem número; portão verde',
            'Pagamento costuma atrasar 1 semana',
            'Cliente bom pagador',
            'Combinar com vizinho se ausente',
        ];

        $entregas   = ['final do mês','segunda semana do mês','sábado pela manhã','após as 17h','em até 10 dias'];
        $totalVendas = 60;
        $baseData    = Carbon::create(2025, 6, 1);

        for ($n=1; $n <= $totalVendas; $n++) {
            $customerId = $customers[($n-1) % count($customers)];
            $sellerId   = $sellers[  ($n-1) % count($sellers)  ];

            // itens determinísticos: 1..3 produtos
            $itens = [];
            $qtdItens = 1 + (($n % 3)); // 1..3
            for ($k=0; $k < $qtdItens; $k++) {
                $p = $products[($n+$k-1) % $products->count()];
                $itens[] = [
                    'product_id' => $p->id,
                    'qty'        => 1 + (($n + $k) % 2), // 1..2
                    'unit_price' => $p->price,
                    'attributes' => ['cor' => $p->color, 'modelo' => $p->model],
                ];
            }
            $total = collect($itens)->sum(fn($i) => $i['qty'] * $i['unit_price']);

            $dueDay  = 5 + ($n % 20); // 5..24
            $resched = ($n % 10 == 0) ? ($dueDay+2 <= 28 ? $dueDay+2 : null) : null;

            DB::transaction(function () use ($n,$customerId,$sellerId,$itens,$total,$dueDay,$resched,$baseData,$obs,$entregas) {
                $sale = Sale::create([
                    'number'            => 'N'.str_pad($n, 5, '0', STR_PAD_LEFT),
                    'customer_id'       => $customerId,
                    'seller_id'         => $sellerId,
                    'total'             => $total,
                    'installments_qty'  => 6 + ($n % 7), // 6..12
                    'due_day'           => $dueDay,
                    'rescheduled_day'   => $resched,
                    'charge_start_date' => $baseData->copy()->addDays($n)->toDateString(),
                    'delivery_text'     => $entregas[$n % count($entregas)],
                    'gps_lat'           => null,
                    'gps_lng'           => null,
                    'collection_note'   => $obs[$n % count($obs)],
                    'status'            => 'aberto', // sem fechar aqui
                ]);

                foreach ($itens as $i) {
                    $sale->items()->create($i);
                }

                // Gera parcelas
                SaleService::createInstallments($sale);
            });
        }
    }
}
