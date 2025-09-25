@extends('layouts.admin')
@section('title', __('global.financial_reports'))

@section('content')
<div class="card shadow-sm pm-card mb-3">
  <div class="card-body">
    <form method="GET" action="{{ route('admin.financial-reports.index') }}" class="row g-3">
      {{-- Nomes de vendedores (checkbox múltiplo, se você quiser listar alguns nomes conhecidos;
           se preferir, remova esta seção e deixe apenas user_type) --}}
      @if(!empty($options['seller_names'] ?? []))
      <div class="col-12">
        <div class="fw-bold mb-1">{{ __('global.seller_plural') }}</div>
        <div class="d-flex flex-wrap gap-3">
          @foreach($options['seller_names'] as $name)
            @php $checked = in_array($name, $filters['user_name'] ?? []); @endphp
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="user_name[]" id="seller-{{ md5($name) }}" value="{{ $name }}" {{ $checked ? 'checked' : '' }}>
              <label class="form-check-label" for="seller-{{ md5($name) }}">{{ $name }}</label>
            </div>
          @endforeach
        </div>
      </div>
      @endif

      {{-- Tipos de vendedor --}}
      <div class="col-12">
        <div class="fw-bold mb-1">{{ __('global.seller_type_plural') }}</div>
        <div class="d-flex flex-wrap gap-3">
          @foreach(($options['user_types'] ?? []) as $type)
            @php $checked = in_array($type, $filters['user_type'] ?? []); @endphp
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="user_type[]" id="ut-{{ md5($type) }}" value="{{ $type }}" {{ $checked ? 'checked' : '' }}>
              <label class="form-check-label" for="ut-{{ md5($type) }}">{{ __('global.seller_type_'.$type) }}</label>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Modo de loja --}}
      <div class="col-12">
        <div class="fw-bold mb-1">{{ __('global.store_mode_plural') }}</div>
        <div class="d-flex flex-wrap gap-3">
          @foreach(($options['store_modes'] ?? []) as $mode)
            @php $checked = in_array($mode, $filters['store_mode'] ?? []); @endphp
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="store_mode[]" id="sm-{{ md5($mode) }}" value="{{ $mode }}" {{ $checked ? 'checked' : '' }}>
              <label class="form-check-label" for="sm-{{ md5($mode) }}">{{ __('global.store_mode_'.$mode) }}</label>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Métodos de pagamento --}}
      <div class="col-12">
        <div class="fw-bold mb-1">{{ __('global.payment_method_plural') }}</div>
        <div class="d-flex flex-wrap gap-3">
          @foreach(($options['payment_methods'] ?? []) as $method)
            @php $checked = in_array($method, $filters['payment_method'] ?? []); @endphp
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="payment_method[]" id="pm-{{ md5($method) }}" value="{{ $method }}" {{ $checked ? 'checked' : '' }}>
              <label class="form-check-label" for="pm-{{ md5($method) }}">{{ $method }}</label>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Cidades --}}
      <div class="col-12">
        <div class="fw-bold mb-1">{{ __('global.city_plural') }}</div>
        <div class="d-flex flex-wrap gap-3">
          @foreach(($options['cities'] ?? []) as $city)
            @php $checked = in_array($city, $filters['customer_city'] ?? []); @endphp
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="customer_city[]" id="city-{{ md5($city) }}" value="{{ $city }}" {{ $checked ? 'checked' : '' }}>
              <label class="form-check-label" for="city-{{ md5($city) }}">{{ $city }}</label>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Produtos (por nome). Se quiser por ID, troque para product_id[] e ajuste o service. --}}
      <div class="col-12">
        <div class="fw-bold mb-1">{{ __('global.product_plural') }}</div>
        <div class="d-flex flex-wrap gap-3">
          @foreach(($options['products'] ?? []) as $pname)
            @php $checked = in_array($pname, $filters['product_name'] ?? []); @endphp
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="product_name[]" id="prod-{{ md5($pname) }}" value="{{ $pname }}" {{ $checked ? 'checked' : '' }}>
              <label class="form-check-label" for="prod-{{ md5($pname) }}">{{ $pname }}</label>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Período predefinido --}}
      <div class="col-md-3">
        <label class="form-label">{{ __('global.date_range') }}</label>
        <select name="period" class="form-select pm-select">
          <option value="">{{ __('global.period_custom') }}</option>
          @php $p = $filters['period'] ?? ''; @endphp
          <option value="this_week"  {{ $p==='this_week'  ? 'selected':'' }}>{{ __('global.this_week') }}</option>
          <option value="last_week"  {{ $p==='last_week'  ? 'selected':'' }}>{{ __('global.last_week') }}</option>
          <option value="this_month" {{ $p==='this_month' ? 'selected':'' }}>{{ __('global.this_month') }}</option>
          <option value="last_month" {{ $p==='last_month' ? 'selected':'' }}>{{ __('global.last_month') }}</option>
          <option value="this_year"  {{ $p==='this_year'  ? 'selected':'' }}>{{ __('global.this_year') }}</option>
          <option value="last_year"  {{ $p==='last_year'  ? 'selected':'' }}>{{ __('global.last_year') }}</option>
        </select>
      </div>

      {{-- Intervalo manual --}}
      <div class="col-md-2">
        <label class="form-label">{{ __('global.from') }}</label>
        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control pm-input">
      </div>
      <div class="col-md-2">
        <label class="form-label">{{ __('global.to') }}</label>
        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control pm-input">
      </div>

      {{-- Botões --}}
      @php
        $filtersForCheck = $filters ?? [];
        unset($filtersForCheck['page']);
        $hasFilters = collect($filtersForCheck)->filter(function($v){
          if (is_array($v)) return !empty(array_filter($v, fn($x)=>$x!=='' && $x!==null));
          return $v !== null && $v !== '';
        })->isNotEmpty();
      @endphp
      <div class="col-md-5 d-flex align-items-end gap-2">
        <button class="btn pm-btn pm-btn-primary">
          {{ __('global.search') }}
        </button>
        <a href="{{ $hasFilters ? route('admin.financial-reports.index') : '#' }}"
           class="btn pm-btn pm-btn-outline-secondary {{ $hasFilters ? '' : 'disabled' }}"
           @if(!$hasFilters) aria-disabled="true" tabindex="-1" @endif>
          {{ __('global.clear') }}
        </a>
      </div>
    </form>

    {{-- Chips legíveis dos filtros --}}
    @if(!empty($chips))
      <div class="mt-3">
        <span class="fw-bold me-2">{{ __('global.filters') }}:</span>
        @foreach($chips as $chip)
          <span class="badge text-bg-secondary me-1">{{ $chip }}</span>
        @endforeach
      </div>
    @endif
  </div>
</div>

<div class="card shadow-sm pm-card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
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
              $methods  = collect($sale->payments ?? [])->pluck('method')->unique()->implode(', ');
              $prods    = collect($sale->items ?? [])->map(fn($it)=>$it->product->name ?? '')->filter()->unique()->implode(', ');
            @endphp
            <tr>
              <td>{{ ($sales->firstItem() ?? 0) + $i }}</td>
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
        {{ $sales->appends(request()->except('page'))->onEachSide(1)->links('pagination::bootstrap-5') }}
      </nav>
    </div>
  </div>
</div>
@endsection
