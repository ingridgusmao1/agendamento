@extends('layouts.admin')
@section('title', __('global.financial_reports'))

@section('content')

@php
  // Totais do conjunto filtrado
  $allTotals = $totals['all'] ?? ['sold'=>0,'received'=>0,'outstanding'=>0];
  $fmt = fn($v) => 'R$ ' . number_format((float)$v, 2, ',', '.');

  // Helpers p/ abrir seções que têm filtros marcados
  $has = fn($k) => !empty(array_filter(($filters[$k] ?? []), fn($v)=>$v!=='' && $v!==null));
  $open = [
      'user_type'      => $has('user_type'),
      'store_mode'     => $has('store_mode'),
      'payment_method' => $has('payment_method'),
      'customer_city'  => $has('customer_city'),
      'product_name'   => $has('product_name'),
      'period'         => !empty($filters['period']) || !empty($filters['from']) || !empty($filters['to']),
  ];
@endphp

<form method="GET" action="{{ route('admin.financial-reports.index') }}" class="mb-3">

  {{-- BLOCO DE BOTÕES (SEMPRE VISÍVEL) --}}
  <div class="d-flex justify-content-end gap-2 mb-2">
    @php
      $filtersForCheck = $filters ?? [];
      unset($filtersForCheck['page']);
      $hasFilters = collect($filtersForCheck)->filter(function($v){
        if (is_array($v)) return !empty(array_filter($v, fn($x)=>$x!=='' && $x!==null));
        return $v !== null && $v !== '';
      })->isNotEmpty();
    @endphp

    <a href="{{ $hasFilters ? route('admin.financial-reports.index') : '#' }}"
       class="btn pm-btn pm-btn-outline-secondary {{ $hasFilters ? '' : 'disabled' }}"
       @if(!$hasFilters) aria-disabled="true" tabindex="-1" @endif>
      {{ __('global.clear') }}
    </a>

    <button class="btn pm-btn pm-btn-primary">
      {{ __('global.search') }}
    </button>
  </div>

  {{-- ACCORDION DE FILTROS --}}
  <div class="accordion" id="filtersAccordion">

    {{-- Tipos de vendedor --}}
    <div class="accordion-item">
      <h2 class="accordion-header" id="hdrUserType">
        <button class="accordion-button {{ $open['user_type'] ? '' : 'collapsed' }}" type="button"
                data-bs-toggle="collapse" data-bs-target="#accUserType" aria-expanded="{{ $open['user_type'] ? 'true' : 'false' }}"
                aria-controls="accUserType">
          {{ __('global.seller_type_plural') }}
        </button>
      </h2>
      <div id="accUserType" class="accordion-collapse collapse {{ $open['user_type'] ? 'show' : '' }}" aria-labelledby="hdrUserType" data-bs-parent="#filtersAccordion">
        <div class="accordion-body">
          <div class="d-flex flex-column gap-2 ps-2">
            @foreach(($options['user_types'] ?? []) as $type)
              @php $checked = in_array($type, $filters['user_type'] ?? []); @endphp
              <div class="form-check ms-1">
                <input class="form-check-input" type="checkbox" name="user_type[]" id="ut-{{ md5($type) }}" value="{{ $type }}" {{ $checked ? 'checked' : '' }}>
                <label class="form-check-label" for="ut-{{ md5($type) }}">{{ __('global.seller_type_'.$type) }}</label>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- Modos de loja --}}
    <div class="accordion-item">
      <h2 class="accordion-header" id="hdrStoreMode">
        <button class="accordion-button {{ $open['store_mode'] ? '' : 'collapsed' }}" type="button"
                data-bs-toggle="collapse" data-bs-target="#accStoreMode" aria-expanded="{{ $open['store_mode'] ? 'true' : 'false' }}"
                aria-controls="accStoreMode">
          {{ __('global.store_mode_plural') }}
        </button>
      </h2>
      <div id="accStoreMode" class="accordion-collapse collapse {{ $open['store_mode'] ? 'show' : '' }}" aria-labelledby="hdrStoreMode" data-bs-parent="#filtersAccordion">
        <div class="accordion-body">
          <div class="d-flex flex-column gap-2 ps-2">
            @foreach(($options['store_modes'] ?? []) as $mode)
              @php $checked = in_array($mode, $filters['store_mode'] ?? []); @endphp
              <div class="form-check ms-1">
                <input class="form-check-input" type="checkbox" name="store_mode[]" id="sm-{{ md5($mode) }}" value="{{ $mode }}" {{ $checked ? 'checked' : '' }}>
                <label class="form-check-label" for="sm-{{ md5($mode) }}">{{ __('global.store_mode_'.$mode) }}</label>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- Métodos de pagamento --}}
    <div class="accordion-item">
      <h2 class="accordion-header" id="hdrPaymentMethod">
        <button class="accordion-button {{ $open['payment_method'] ? '' : 'collapsed' }}" type="button"
                data-bs-toggle="collapse" data-bs-target="#accPaymentMethod" aria-expanded="{{ $open['payment_method'] ? 'true' : 'false' }}"
                aria-controls="accPaymentMethod">
          {{ __('global.payment_method_plural') }}
        </button>
      </h2>
      <div id="accPaymentMethod" class="accordion-collapse collapse {{ $open['payment_method'] ? 'show' : '' }}" aria-labelledby="hdrPaymentMethod" data-bs-parent="#filtersAccordion">
        <div class="accordion-body">
          <div class="d-flex flex-column gap-2 ps-2">
            @foreach(($options['payment_methods'] ?? []) as $method)
              @php $checked = in_array($method, $filters['payment_method'] ?? []); @endphp
              <div class="form-check ms-1">
                <input class="form-check-input" type="checkbox" name="payment_method[]" id="pm-{{ md5($method) }}" value="{{ $method }}" {{ $checked ? 'checked' : '' }}>
                <label class="form-check-label" for="pm-{{ md5($method) }}">{{ $method }}</label>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- Cidades --}}
    <div class="accordion-item">
      <h2 class="accordion-header" id="hdrCities">
        <button class="accordion-button {{ $open['customer_city'] ? '' : 'collapsed' }}" type="button"
                data-bs-toggle="collapse" data-bs-target="#accCities" aria-expanded="{{ $open['customer_city'] ? 'true' : 'false' }}"
                aria-controls="accCities">
          {{ __('global.city_plural') }}
        </button>
      </h2>
      <div id="accCities" class="accordion-collapse collapse {{ $open['customer_city'] ? 'show' : '' }}" aria-labelledby="hdrCities" data-bs-parent="#filtersAccordion">
        <div class="accordion-body">
          <div class="d-flex flex-column gap-2 ps-2">
            @foreach(($options['cities'] ?? []) as $city)
              @php $checked = in_array($city, $filters['customer_city'] ?? []); @endphp
              <div class="form-check ms-1">
                <input class="form-check-input" type="checkbox" name="customer_city[]" id="city-{{ md5($city) }}" value="{{ $city }}" {{ $checked ? 'checked' : '' }}>
                <label class="form-check-label" for="city-{{ md5($city) }}">{{ $city }}</label>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- Produtos --}}
    <div class="accordion-item">
      <h2 class="accordion-header" id="hdrProducts">
        <button class="accordion-button {{ $open['product_name'] ? '' : 'collapsed' }}" type="button"
                data-bs-toggle="collapse" data-bs-target="#accProducts" aria-expanded="{{ $open['product_name'] ? 'true' : 'false' }}"
                aria-controls="accProducts">
          {{ __('global.product_plural') }}
        </button>
      </h2>
      <div id="accProducts" class="accordion-collapse collapse {{ $open['product_name'] ? 'show' : '' }}" aria-labelledby="hdrProducts" data-bs-parent="#filtersAccordion">
        <div class="accordion-body">
          <div class="d-flex flex-column gap-2 ps-2">
            @foreach(($options['products'] ?? []) as $pname)
              @php $checked = in_array($pname, $filters['product_name'] ?? []); @endphp
              <div class="form-check ms-1">
                <input class="form-check-input" type="checkbox" name="product_name[]" id="prod-{{ md5($pname) }}" value="{{ $pname }}" {{ $checked ? 'checked' : '' }}>
                <label class="form-check-label" for="prod-{{ md5($pname) }}">{{ $pname }}</label>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- Período --}}
    <div class="accordion-item">
      <h2 class="accordion-header" id="hdrPeriod">
        <button class="accordion-button {{ $open['period'] ? '' : 'collapsed' }}" type="button"
                data-bs-toggle="collapse" data-bs-target="#accPeriod" aria-expanded="{{ $open['period'] ? 'true' : 'false' }}"
                aria-controls="accPeriod">
          {{ __('global.date_range') }}
        </button>
      </h2>
      <div id="accPeriod" class="accordion-collapse collapse {{ $open['period'] ? 'show' : '' }}" aria-labelledby="hdrPeriod" data-bs-parent="#filtersAccordion">
        <div class="accordion-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">{{ __('global.date_range') }}</label>
              @php $p = $filters['period'] ?? ''; @endphp
              <select name="period" class="form-select pm-select">
                <option value="">{{ __('global.period_custom') }}</option>
                <option value="this_week"  {{ $p==='this_week'  ? 'selected':'' }}>{{ __('global.this_week') }}</option>
                <option value="last_week"  {{ $p==='last_week'  ? 'selected':'' }}>{{ __('global.last_week') }}</option>
                <option value="this_month" {{ $p==='this_month' ? 'selected':'' }}>{{ __('global.this_month') }}</option>
                <option value="last_month" {{ $p==='last_month' ? 'selected':'' }}>{{ __('global.last_month') }}</option>
                <option value="this_year"  {{ $p==='this_year'  ? 'selected':'' }}>{{ __('global.this_year') }}</option>
                <option value="last_year"  {{ $p==='last_year'  ? 'selected':'' }}>{{ __('global.last_year') }}</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">{{ __('global.from') }}</label>
              <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control pm-input">
            </div>
            <div class="col-md-2">
              <label class="form-label">{{ __('global.to') }}</label>
              <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control pm-input">
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>{{-- /accordion --}}
</form>

{{-- Totais do conjunto filtrado --}}
<div class="card shadow-sm pm-card mb-3">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <div class="p-3 rounded border bg-light">
          <div class="text-muted small">{{ __('global.total_sold_all') }}</div>
          <div class="fs-4 fw-bold">{{ $fmt($allTotals['sold'] ?? 0) }}</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-3 rounded border bg-light">
          <div class="text-muted small">{{ __('global.total_received_all') }}</div>
          <div class="fs-4 fw-bold">{{ $fmt($allTotals['received'] ?? 0) }}</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-3 rounded border bg-light">
          <div class="text-muted small">{{ __('global.total_outstanding_all') }}</div>
          <div class="fs-4 fw-bold">{{ $fmt($allTotals['outstanding'] ?? 0) }}</div>
        </div>
      </div>
    </div>
    <div class="small text-muted mt-2">
      {{ __('global.totals_note_all_filtered') }}
    </div>
  </div>
</div>

{{-- Chips legíveis dos filtros --}}
@if(!empty($chips))
  <div class="mt-2 mb-3">
    <span class="fw-bold me-2">{{ __('global.filters') }}:</span>
    @foreach($chips as $chip)
      <span class="badge text-bg-secondary me-1">{{ $chip }}</span>
    @endforeach
  </div>
@endif

{{-- Tabela de resultados --}}
<div class="card shadow-sm pm-card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>{{ __('global.customer') }}</th>
            <th>{{ __('global.seller') }}</th>
            <th>{{ __('global.product_plural') }}</th>
            <th>{{ __('global.payment_method') }}</th>
            <th>{{ __('global.city') }}</th>
            <th>{{ __('global.date_range') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($sales as $i => $sale)
            @php
              $customer = $sale->customer;
              $seller   = $sale->seller;
              $city     = $customer->city ?? '-';
              $col      = $payment_column ?? 'note';
              $methods  = collect($sale->payments ?? [])->pluck($col)->filter()->unique()->implode(', ');
              $prods    = collect($sale->items ?? [])
                            ->map(fn($it) => $it->product->name ?? '')
                            ->filter()
                            ->unique()
                            ->implode(', ');
            @endphp
            <tr>
              <td>{{ $customer->name ?? '-' }}</td>
              <td>{{ $seller->name ?? '-' }}</td>
              <td>{{ $prods ?: '-' }}</td>
              <td>{{ $methods ?: '-' }}</td>
              <td>{{ $city }}</td>
              <td>{{ \Illuminate\Support\Carbon::parse($sale->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted">Nenhum resultado</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Resumo da paginação + links --}}
    <div class="d-flex justify-content-between align-items-center">
        <div class="small text-muted">
            @php
            $from = $sales->firstItem() ?? 0;
            $to   = $sales->lastItem() ?? $sales->count();
            $tot  = $sales->total();
            @endphp
            {{ __('global.showing_results', ['from' => $from, 'to' => $to, 'total' => $tot]) }}
        </div>
        <nav>
            {{ $sales->appends(request()->except('page'))->onEachSide(1)->links('pagination::bootstrap-5-custom') }}
        </nav>
    </div>

    <hr>

    {{-- Impressão do relatório --}}
    <div class="d-flex justify-content-end mt-3">
        <a class="btn btn-dark"
            href="{{ route('admin.financial-reports.print', request()->query()) }}"
            target="_blank" rel="noopener">
            {{ __('global.generate_report') }}
        </a>
    </div>
  </div>
</div>

@endsection
