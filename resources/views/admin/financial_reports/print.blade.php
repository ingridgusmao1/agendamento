<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>{{ __('global.financial_reports') }}</title>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#222; }
  h1 { font-size: 18px; margin: 0 0 6px; }
  .muted { color: #555; }
  .summary { border:1px solid #ccc; padding:8px; margin: 10px 0 12px; }
  .grid { width:100%; }
  .grid td { width:33%; vertical-align:top; padding:4px 6px; }
  .chips { margin:8px 0 12px; }
  .chip { display:inline-block; border:1px solid #ccc; border-radius:3px; padding:2px 6px; margin:0 4px 4px 0; font-size:11px; }
  table { width:100%; border-collapse: collapse; }
  th, td { border:1px solid #ddd; padding:6px; }
  th { background:#f2f2f2; text-align:left; }
  .right { text-align:right; }
  footer { position: fixed; bottom: 10px; left: 0; right: 0; font-size:10px; text-align:right; }
</style>
</head>
<body>

  <h1>{{ __('global.financial_report') }}</h1>
  <div class="muted">{{ __('global.generated_at') }}: {{ now()->format('d/m/Y H:i') }}</div>

  {{-- Totais do conjunto filtrado --}}
  @php
    $t = $totals['all'] ?? ['sold'=>0,'received'=>0,'outstanding'=>0];
    $fmt = fn($v) => 'R$ ' . number_format((float)$v, 2, ',', '.');
  @endphp
  <table class="summary grid">
    <tr>
      <td><strong>{{ __('global.total_sold_all') }}:</strong> {{ $fmt($t['sold'] ?? 0) }}</td>
      <td><strong>{{ __('global.total_received_all') }}:</strong> {{ $fmt($t['received'] ?? 0) }}</td>
      <td><strong>{{ __('global.total_outstanding_all') }}:</strong> {{ $fmt($t['outstanding'] ?? 0) }}</td>
    </tr>
  </table>

  {{-- Filtros aplicados (chips) --}}
  @if(!empty($chips))
    <div class="chips">
      <strong>{{ __('global.applied_filters') }}:</strong>
      @foreach($chips as $chip)
        <span class="chip">{{ $chip }}</span>
      @endforeach
    </div>
  @endif

  {{-- Tabela completa --}}
  <table>
    <thead>
      <tr>
        <th>{{ __('global.customer') }}</th>
        <th>{{ __('global.seller') }}</th>
        <th>{{ __('global.product_plural') }}</th>
        <th>{{ __('global.note') }}</th>
        <th>{{ __('global.city') }}</th>
        <th>{{ __('global.date_range') }}</th>
      </tr>
    </thead>
    <tbody>
      @foreach($sales as $i => $sale)
        @php
          $customer = $sale->customer;
          $seller   = $sale->seller;
          $city     = $customer->city ?? '-';
          $col      = $payment_column ?? 'note';
          $methods  = collect($sale->payments ?? [])->pluck($col)->filter()->unique()->implode(', ');
          $prods    = collect($sale->items ?? [])
                        ->map(fn($it) => $it->product->name ?? '')
                        ->filter()->unique()->implode(', ');
        @endphp
        <tr>
          <td>{{ $customer->name ?? '-' }}</td>
          <td>{{ $seller->name ?? '-' }}</td>
          <td>{{ $prods ?: '-' }}</td>
          <td>{{ $methods ?: '-' }}</td>
          <td>{{ $city }}</td>
          <td>{{ \Illuminate\Support\Carbon::parse($sale->created_at)->format('d/m/Y H:i') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
