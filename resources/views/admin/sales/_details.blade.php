@php
  // Helpers locais
  $money = fn($v) => is_numeric($v) ? number_format((float)$v, 2, ',', '.') : '-';

  // primeira foto do produto (photo_path pode ser array/JSON/string/null)
  $firstPhoto = function ($product) {
    if (!$product) return null;
    $pp = $product->photo_path ?? null;
    $arr = is_array($pp) ? $pp : (is_string($pp) ? (json_decode($pp, true) ?: []) : []);
    return $arr[0] ?? null;
  };

  // avatar do cliente
  $cust = $sale->customer ?? null;
  $avatar = $cust && !empty($cust->avatar_path) ? asset('storage/'.$cust->avatar_path) : 'https://via.placeholder.com/120?text=Avatar';

  // totais básicos
  $itemsTotal = $sale->items?->sum(function($it){
      $qty = $it->qty ?? $it->quantity ?? 0;
      $price = $it->price ?? $it->unit_price ?? 0;
      return (float)$qty * (float)$price;
  }) ?? 0.0;

  $paymentsTotal = $sale->payments?->sum(function($p){
      return (float)($p->amount ?? $p->value ?? 0);
  }) ?? 0.0;

  $balance = $itemsTotal - $paymentsTotal;
@endphp

<div class="container-fluid py-3">

  {{-- Cabeçalho da venda --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
    <div>
      <h5 class="mb-1">{{ $sale->number }}</h5>
      <div class="text-muted small">
        {{ __('global.created_at') }}:
        {{ optional($sale->created_at)->format('d/m/Y H:i') ?? '-' }}
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      @php
        $status = (string)($sale->status ?? '—');
        $badge = match ($status) {
          'fechado' => 'bg-success',
          'atrasado' => 'bg-warning text-dark',
          default => 'bg-secondary'
        };
      @endphp
      <span class="badge {{ $badge }}">{{ ucfirst($status) }}</span>
    </div>
  </div>

  {{-- Cliente --}}
  <div class="card mb-3">
    <div class="card-body d-flex align-items-center gap-3">
      <img src="{{ $avatar }}" alt="avatar" class="rounded" style="width:80px;height:80px;object-fit:cover;">
      <div class="flex-grow-1">
        <div class="fw-semibold">{{ $cust->name ?? '-' }}</div>
        <div class="text-muted small">
          {{ $cust->cpf ?? '-' }} · {{ $cust->phone ?? '-' }}
        </div>
        <div class="text-muted small">
          {{ $cust->street ?? '-' }}, {{ $cust->number ?? '-' }} - {{ $cust->district ?? '-' }} · {{ $cust->city ?? '-' }}
        </div>
      </div>
    </div>
  </div>

  {{-- Itens da venda --}}
  <div class="card mb-3">
    <div class="card-header fw-semibold">{{ __('global.items') }}</div>
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead>
          <tr>
            <th style="width:80px">{{ __('global.photo') }}</th>
            <th>{{ __('global.product') }}</th>
            <th class="text-end" style="width:120px">{{ __('global.qty') }}</th>
            <th class="text-end" style="width:140px">{{ __('global.price') }}</th>
            <th class="text-end" style="width:160px">{{ __('global.subtotal') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($sale->items ?? [] as $it)
            @php
              $prod = $it->product ?? null;
              $photo = $firstPhoto($prod);
              $qty = $it->qty ?? $it->quantity ?? 0;
              $price = $it->price ?? $it->unit_price ?? 0;
              $subtotal = (float)$qty * (float)$price;
              $attrs = $it->attributes ?? null;
            @endphp
            <tr>
              <td>
                @if($photo)
                  <img src="{{ asset('storage/'.$photo) }}" class="img-thumbnail" style="width:64px;height:64px;object-fit:cover;" alt="prod">
                @else
                  <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width:64px;height:64px;">—</div>
                @endif
              </td>
              <td>
                {{ $prod->name ?? '-' }}
                {{-- Exibir atributos abaixo do nome, se existirem --}}
                @if(!empty($attrs) && is_array($attrs))
                  <div class="text-muted small">
                    Atributos: {{ implode(', ', $attrs) }}
                  </div>
                @endif
              </td>
              <td class="text-end">{{ $qty }}</td>
              <td class="text-end">R$ {{ $money($price) }}</td>
              <td class="text-end">R$ {{ $money($subtotal) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">{{ __('global.no_results') }}</td>
            </tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-end">{{ __('global.total') }}</th>
            <th class="text-end">R$ {{ $money($itemsTotal) }}</th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  {{-- Parcelas e pagamentos --}}
  <div class="row g-3">

    {{-- Parcelas --}}
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header fw-semibold">{{ __('global.installments') }}</div>
        <div class="table-responsive">
          <table class="table mb-0 align-middle">
            <thead>
              <tr>
                <th style="width:80px">#</th>
                <th>{{ __('global.due_date') }}</th>
                <th class="text-end">{{ __('global.amount') }}</th>
                <th class="text-center">{{ __('global.status') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse($sale->installments ?? [] as $idx => $inst)
                @php
                  $due = $inst->due_date ?? $inst->due_at ?? null;
                  $amount = $inst->amount ?? $inst->value ?? 0;
                  $istatus = (string)($inst->status ?? '—');
                  $ibadge = match ($istatus) {
                    'pago','paid' => 'bg-success',
                    'atrasado','late' => 'bg-warning text-dark',
                    default => 'bg-secondary'
                  };
                @endphp
                <tr>
                  <td>{{ $idx+1 }}</td>
                  <td>{{ $due ? \Illuminate\Support\Carbon::parse($due)->format('d/m/Y') : '-' }}</td>
                  <td class="text-end">R$ {{ $money($amount) }}</td>
                  <td class="text-center"><span class="badge {{ $ibadge }}">{{ ucfirst($istatus) }}</span></td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted py-4">{{ __('global.no_results') }}</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Pagamentos --}}
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header fw-semibold">{{ __('global.payments') }}</div>
        <div class="table-responsive">
          <table class="table mb-0 align-middle">
            <thead>
              <tr>
                <th>{{ __('global.paid_at') }}</th>
                <th class="text-end">{{ __('global.amount') }}</th>
                <th>{{ __('global.payment_method') }}</th>
                <th>{{ __('global.note') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse(($sale->payments ?? collect()) as $i => $pay)
                @php
                  $paidAt = $pay->paid_at ?? $pay->paid_on ?? $pay->created_at ?? null;
                  $pamount = $pay->amount ?? $pay->value ?? 0;
                  $note    = $pay->note ?? '-';
                  $method  = $pay->payment_method ?? '-';
                @endphp
                <tr>
                  <td class="text-sm-85">{{ $paidAt ? \Illuminate\Support\Carbon::parse($paidAt)->format('d/m/Y H:i') : '-' }}</td>
                  <td class="text-end text-sm-85">R$ {{ $money($pamount) }}</td>
                  <td class="text-sm-85">{{ $method }}</td>
                  <td class="text-sm-85">{{ $note }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted py-4">{{ __('global.no_results') }}</td></tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr>
                <th colspan="2" class="text-end">{{ __('global.total') }}</th>
                <th class="text-end">R$ {{ $money($paymentsTotal) }}</th>
                <th></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

  </div>

  {{-- Resumo financeiro --}}
  <div class="card mt-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-12 col-md-4">
          <div class="d-flex justify-content-between">
            <span class="text-muted">{{ __('global.items_total') }}</span>
            <span class="fw-semibold">R$ {{ $money($itemsTotal) }}</span>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="d-flex justify-content-between">
            <span class="text-muted">{{ __('global.payments_total') }}</span>
            <span class="fw-semibold">R$ {{ $money($paymentsTotal) }}</span>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="d-flex justify-content-between">
            <span class="text-muted">{{ __('global.balance') }}</span>
            <span class="fw-bold {{ $balance <= 0 ? 'text-success' : 'text-danger' }}">
              R$ {{ $money($balance) }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
