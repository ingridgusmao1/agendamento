<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{User, Customer, Product, Sale};

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $sellers   = User::whereIn('type', ['vendedor','vendedor_cobrador'])->pluck('id')->values();
        $customers = Customer::orderBy('id')->pluck('id')->values();
        $products  = Product::orderBy('id')->get();

        if ($sellers->isEmpty() || $customers->isEmpty() || $products->isEmpty()) {
            $this->command?->warn('⚠️ Faltam users/customers/products para gerar sales.');
            return;
        }

        // Centros no Nordeste
        $centers = [
            ['lat' => -3.7319,  'lng' => -38.5267], // Fortaleza
            ['lat' => -5.7945,  'lng' => -35.2110], // Natal
            ['lat' => -8.0476,  'lng' => -34.8770], // Recife
            ['lat' => -9.6658,  'lng' => -35.7353], // Maceió
            ['lat' => -10.9472, 'lng' => -37.0731], // Aracaju
            ['lat' => -12.9777, 'lng' => -38.5016], // Salvador
            ['lat' => -7.1195,  'lng' => -34.8450], // João Pessoa
            ['lat' => -5.0919,  'lng' => -42.8034], // Teresina
            ['lat' => -2.5391,  'lng' => -44.2825], // São Luís
        ];
        $jitter = fn(float $v) => $v + mt_rand(-15, 15) / 100; // ±0.15°

        $obs       = [
            'Cobrar no posto de gasolina',
            'Entregar na casa da sogra',
            'Cliente trabalha à tarde, cobrar de manhã',
            'Evitar horário de almoço',
            'Casa sem número; portão verde',
            'Pagamento costuma atrasar 1 semana',
            'Cliente bom pagador',
            'Combinar com vizinho se ausente',
        ];
        $entregas  = ['final do mês','segunda semana do mês','sábado pela manhã','após as 17h','em até 10 dias'];

        $totalVendas = 60;
        $today       = Carbon::today()->startOfDay();

        // Distribuição desejada do PRIMEIRO vencimento por venda:
        // ~10% atrasado, ~30% hoje, ~25% +3d, ~20% +5d, ~15% futuro (>5d)
        $bucketFor = function(int $n) {
            $r = ($n * 37) % 100; // pseudo-aleatório determinístico por n
            return match (true) {
                $r < 10   => 'overdue',   // 10%
                $r < 40   => 'today',     // 30%
                $r < 65   => 'plus3',     // 25%
                $r < 85   => 'plus5',     // 20%
                default   => 'future',    // 15%
            };
        };

        $targetDateFor = function(string $bucket, Carbon $today) {
            return match ($bucket) {
                'overdue' => $today->copy()->subDays(mt_rand(1, 3)),   // pouco atrasado
                'today'   => $today->copy(),
                'plus3'   => $today->copy()->addDays(3),
                'plus5'   => $today->copy()->addDays(5),
                'future'  => $today->copy()->addDays(mt_rand(8, 25)),
            };
        };

        for ($n = 1; $n <= $totalVendas; $n++) {
            $customerId = $customers[($n-1) % $customers->count()];
            $sellerId   = $sellers[  ($n-1) % $sellers->count()  ];

            // itens 1..3
            $items = [];
            $qtdItens = 1 + ($n % 3);
            for ($k = 0; $k < $qtdItens; $k++) {
                $p = $products[($n+$k-1) % $products->count()];
                $items[] = [
                    'product_id' => $p->id,
                    'qty'        => 1 + (($n + $k) % 2), // 1..2
                    'unit_price' => $p->price,
                    'attributes' => ['cor' => $p->color, 'modelo' => $p->model],
                ];
            }
            $total = collect($items)->sum(fn($i) => $i['qty'] * $i['unit_price']);

            // bucket → primeiro vencimento alvo
            $bucket     = $bucketFor($n);
            $firstDue   = $targetDateFor($bucket, $today);
            $dueDay     = min(28, (int)$firstDue->day); // cap a 28
            // Para que o primeiro vencimento caia exatamente em $firstDue:
            // defina charge_start_date para um dia ANTES do due (assim o algoritmo do InstallmentSeeder acerta o 1º vencimento)
            $chargeStart = $firstDue->copy()->subDay(); // start < firstDue e no mesmo mês
            $createdAt   = $chargeStart->copy()->subDays(rand(0, 5));

            // GPS NE
            $c   = $centers[array_rand($centers)];
            $lat = $jitter($c['lat']);
            $lng = $jitter($c['lng']);

            DB::transaction(function () use (
                $n,
                $customerId,
                $sellerId,
                $items,
                $total,
                $dueDay,
                $chargeStart,
                $entregas,
                $obs,
                $lat,
                $lng,
                $createdAt   // 👈 ADICIONE ESTA LINHA
            ) {
                $sale = Sale::create([
                    'number'            => 'N'.str_pad($n, 5, '0', STR_PAD_LEFT),
                    'customer_id'       => $customerId,
                    'seller_id'         => $sellerId,
                    'total'             => $total,
                    'installments_qty'  => 6 + ($n % 7), // 6..12
                    'due_day'           => $dueDay,
                    'rescheduled_day'   => null,
                    'charge_start_date' => $chargeStart->toDateString(),
                    'delivery_text'     => $entregas[$n % count($entregas)],
                    'gps_lat'           => $lat,
                    'gps_lng'           => $lng,
                    'collection_note'   => $obs[$n % count($obs)],
                    'status'            => 'aberto',
                    'created_at'        => $createdAt,
                    'updated_at'        => now(),
                ]);

                foreach ($items as $i) {
                    $sale->items()->create($i);
                }
            });
        }
    }
}
