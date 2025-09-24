@extends('layouts.admin')
@section('title', __('global.financial_reports'))

@section('content')
<div class="card shadow-sm pm-card mb-3">
  <div class="card-body">
    <form method="GET" action="{{ route('admin.financial-reports.index') }}" class="row g-2">
      <div class="col-md-3">
        <input type="text" name="user_name" value="{{ $filters['user_name'] ?? '' }}"
               placeholder="Nome do vendedor" class="form-control pm-input">
      </div>

      <div class="col-md-2">
        <select name="user_type" class="form-select pm-select">
          <option value="">Tipo</option>
          <option value="vendedor" {{ ($filters['user_type'] ?? '')=='vendedor'?'selected':'' }}>Vendedor</option>
          <option value="cobrador" {{ ($filters['user_type'] ?? '')=='cobrador'?'selected':'' }}>Cobrador</option>
          <option value="vendedor_cobrador" {{ ($filters['user_type'] ?? '')=='vendedor_cobrador'?'selected':'' }}>Vendedor/Cobrador</option>
        </select>
      </div>

      <div class="col-md-2">
        <select name="store_mode" class="form-select pm-select">
          <option value="">Modalidade</option>
          <option value="externo" {{ ($filters['store_mode'] ?? '')=='externo'?'selected':'' }}>Externo</option>
          <option value="loja"    {{ ($filters['store_mode'] ?? '')=='loja'?'selected':'' }}>Loja</option>
          <option value="ambos"   {{ ($filters['store_mode'] ?? '')=='ambos'?'selected':'' }}>Ambos</option>
          <option value="outro"   {{ ($filters['store_mode'] ?? '')=='outro'?'selected':'' }}>Outro</option>
        </select>
      </div>

      <div class="col-md-2">
        <input type="text" name="product_name" value="{{ $filters['product_name'] ?? '' }}"
               placeholder="Produto" class="form-control pm-input">
      </div>

      <div class="col-md-2">
        <input type="text" name="customer_city" value="{{ $filters['customer_city'] ?? '' }}"
               placeholder="Cidade do cliente" class="form-control pm-input">
      </div>

      <div class="col-md-2">
        <select name="payment_method" class="form-select pm-select">
          <option value="">Forma de Pagamento</option>
          <option value="avista"    {{ ($filters['payment_method'] ?? '')=='avista'?'selected':'' }}>À vista</option>
          <option value="credito"   {{ ($filters['payment_method'] ?? '')=='credito'?'selected':'' }}>Cartão de crédito</option>
          <option value="crediario" {{ ($filters['payment_method'] ?? '')=='crediario'?'selected':'' }}>Crediário</option>
          <option value="outro"     {{ ($filters['payment_method'] ?? '')=='outro'?'selected':'' }}>Outro</option>
        </select>
      </div>

      <div class="col-md-2">
        <select name="period" class="form-select pm-select">
          <option value="">Intervalo</option>
          <option value="last_week"  {{ ($filters['period'] ?? '')=='last_week'?'selected':'' }}>Última semana</option>
          <option value="this_week"  {{ ($filters['period'] ?? '')=='this_week'?'selected':'' }}>Esta semana</option>
          <option value="last_month" {{ ($filters['period'] ?? '')=='last_month'?'selected':'' }}>Último mês</option>
          <option value="this_month" {{ ($filters['period'] ?? '')=='this_month'?'selected':'' }}>Este mês</option>
          <option value="last_year"  {{ ($filters['period'] ?? '')=='last_year'?'selected':'' }}>Último ano</option>
          <option value="this_year"  {{ ($filters['period'] ?? '')=='this_year'?'selected':'' }}>Este ano</option>
        </select>
      </div>

      <div class="col-md-2">
        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control pm-input">
      </div>

      <div class="col-md-2">
        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control pm-input">
      </div>

      @php
        $filtersForCheck = $filters ?? [];
        unset($filtersForCheck['page']);
        $hasFilters = collect($filtersForCheck)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();
      @endphp
      <div class="col-md-2 d-flex gap-2">
        <button class="btn pm-btn pm-btn-primary w-50 justify-content-center">
          {{ __('global.search') }}
        </button>
        <a href="{{ $hasFilters ? route('admin.financial-reports.index') : '#' }}"
           class="btn pm-btn pm-btn-outline-secondary w-50 justify-content-center {{ $hasFilters ? '' : 'disabled' }}"
           @if(!$hasFilters) aria-disabled="true" tabindex="-1" @endif>
          {{ __('global.clear') ?? 'Limpar' }}
        </a>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm pm-card">
  <div class="card-body">
    <h5 class="card-title pm-card-title">Resultados</h5>
    <p class="text-muted">Filtros: {{ json_encode(array_filter($filters ?? [])) ?: 'nenhum' }}</p>

    <div class="table-responsive">
      <table class="table table-bordered pm-table align-middle">
        <thead class="table-light">
          <tr>
            @if(empty($filters['user_name']))
              <th>Vendedor</th>
            @endif
            @if(empty($filters['user_type']))
              <th>Tipo</th>
            @endif
            @if(empty($filters['store_mode']))
              <th>Modalidade</th>
            @endif
            @if(empty($filters['product_name']))
              <th>Produto</th>
            @endif
            @if(empty($filters['customer_city']))
              <th>Cidade</th>
            @endif
            @if(empty($filters['payment_method']))
              <th>Forma de Pagamento</th>
            @endif
            <th>Valor</th>
            <th>Data</th>
          </tr>
        </thead>
        <tbody>
          @forelse($sales as $s)
            <tr>
              @if(empty($filters['user_name']))
                <td>{{ $s->seller->name ?? '-' }}</td>
              @endif
              @if(empty($filters['user_type']))
                <td>{{ $s->seller->type ?? '-' }}</td>
              @endif
              @if(empty($filters['store_mode']))
                <td>{{ $s->seller->store_mode ?? '-' }}</td>
              @endif
              @if(empty($filters['product_name']))
                <td>{{ $s->items->pluck('product.name')->filter()->implode(', ') }}</td>
              @endif
              @if(empty($filters['customer_city']))
                <td>{{ $s->customer->city ?? '-' }}</td>
              @endif
              @if(empty($filters['payment_method']))
                <td>{{ $s->payments->pluck('payment_method')->filter()->implode(', ') }}</td>
              @endif
              <td>R$ {{ number_format($s->total, 2, ',', '.') }}</td>
              <td>{{ $s->created_at->format('d/m/Y') }}</td>
            </tr>
          @empty
            <tr><td colspan="10" class="text-center text-muted">Nenhum resultado</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <nav class="d-flex justify-content-end">
      {{ $sales->appends(request()->except('page'))->onEachSide(1)->links('pagination::bootstrap-5') }}
    </nav>
  </div>
</div>
@endsection
