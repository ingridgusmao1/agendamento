@php
  // util: primeira foto do produto a partir do JSON photo_path
  $firstPhoto = function ($product) {
    if (!$product || empty($product->photo_path)) return null;
    $arr = is_array($product->photo_path)
      ? $product->photo_path
      : (is_string($product->photo_path) ? (json_decode($product->photo_path, true) ?: []) : []);
    return $arr[0] ?? null;
  };

  $cust = $sale->customer;
  $custAvatar = $cust && $cust->avatar_path ? asset($cust->avatar_path) : 'https://via.placeholder.com/240?text=Avatar';
@endphp

<div class="row g-3">
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="text-muted">{{ __('global.customer') }}</h6>
        <div class="d-flex gap-3 align-items-center">
          <img src="{{ $custAvatar }}" class="img-thumbnail" style="width:120px;height:120px;object-fit:cover;" alt="avatar">
          <div>
            <div class="fw-semibold">{{ $cust?->name ?? '-' }}</div>
            <div class="text-muted small">{{ $cust?->cpf }}</div>
            <div class="text-muted small">{{ $cust?->phone }}</div>
            <div class="text-muted small">
              {{ $cust?->street }}{{ $cust?->number ? ', '.$cust?->number : '' }} - {{ $cust?->district }} - {{ $cust?->city }}
            </div>
          </div>
        </div>

        <hr>
        <div class="d-flex flex-wrap gap-3">
          <div>
            <div class="text-muted small">{{ __('global.sale_id') }}</div>
            <div>#{{ $sale->id }}</div>
          </div>
          <div>
            <div class="text-muted small">{{ __('global.status') }}</div>
            <div>{{ ucfirst($sale->status) }}</div>
          </div>
          <div>
            <div class="text-muted small">{{ __('global.total') }}</div>
            <div>{{ isset($sale->total) ? number_format($sale->total, 2, ',', '.') : '-' }}</div>
          </div>
          <div>
            <div class="text-muted small">{{ __('global.created_at') }}</div>
            <div>{{ $sale->created_at?->format('d/m/Y H:i') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Itens da venda --}}
  <div class="col-md-8">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-3">{{ __('global.items') }}</h6>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th style="width:80px">{{ __('global.photo') }}</th>
                <th>{{ __('global.product') }}</th>
                <th>{{ __('global.type') }}</th>
                <th class="text-end" style="width:120px">{{ __('global.qty') }}</th>
                <th class="text-end" style="width:140px">{{ __('global.price') }}</th>
                <th class="text-end" style="width:140px">{{ __('global.subtotal') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($sale->items as $it)
              @php
                $p = $it->product ?? null;
                $pPhoto = $p ? $firstPhoto($p) : null;
                $pSrc = $pPhoto ? asset($pPhoto) : 'https://via.placeholder.com/120?text=IMG';
              @endphp
              <tr>
                <td>
                  <img src="{{ $pSrc }}" style="width:60px;height:60px;object-fit:cover;" class="img-thumbnail" alt="prod">
                </td>
                <td>{{ $p?->name ?? '-' }}</td>
                <td>{{ $p?->type ?? '-' }}</td>
                <td class="text-end">{{ number_format((float)($it->quantity ?? 0), 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format((float)($it->price ?? 0), 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format((float)(($it->quantity ?? 0) * ($it->price ?? 0)), 2, ',', '.') }}</td>
              </tr>
              @empty
              <tr><td colspan="6" class="text-center text-muted">{{ __('global.no_items') }}</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Parcelas e Pagamentos --}}
    <div class="row g-3 mt-3">
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h6 class="mb-3">{{ __('global.installments') }}</h6>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>{{ __('global.due_date') }}</th>
                    <th class="text-end">{{ __('global.amount') }}</th>
                    <th>{{ __('global.status') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($sale->installments as $i)
                  <tr>
                    <td>{{ $i->number ?? '-' }}</td>
                    <td>{{ isset($i->due_date) ? \Illuminate\Support\Carbon::parse($i->due_date)->format('d/m/Y') : '-' }}</td>
                    <td class="text-end">{{ number_format((float)($i->amount ?? 0), 2, ',', '.') }}</td>
                    <td>{{ $i->status ?? '-' }}</td>
                  </tr>
                  @empty
                  <tr><td colspan="4" class="text-center text-muted">{{ __('global.no_data') }}</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h6 class="mb-3">{{ __('global.payments') }}</h6>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>{{ __('global.date') }}</th>
                    <th class="text-end">{{ __('global.amount') }}</th>
                    <th>{{ __('global.method') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($sale->payments as $p)
                  <tr>
                    <td>{{ $p->id }}</td>
                    <td>{{ isset($p->paid_at) ? \Illuminate\Support\Carbon::parse($p->paid_at)->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-end">{{ number_format((float)($p->amount ?? 0), 2, ',', '.') }}</td>
                    <td>{{ $p->method ?? '-' }}</td>
                  </tr>
                  @empty
                  <tr><td colspan="4" class="text-center text-muted">{{ __('global.no_data') }}</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
