@extends('layouts.admin')

@section('content')
@php
    use Illuminate\Support\Arr;

    // Normaliza 'origins' vindo da query. Se não houver nada na URL, default = ambos.
    $origins = Arr::wrap(request()->input('origins', request()->has('origins') ? [] : ['store','external']));
@endphp

<style>
  /* Rosa para “perto de vencer” */
  .table-rose      { background-color: #ffe5ec !important; }
  .table-rose-soft { background-color: #fff0f4 !important; }
</style>

<div class="container-fluid mt-3">
  <h4 class="mb-3">{{ __('global.installments_schedule_title') }}</h4>

  <!-- Filtros (APENAS checkboxes) -->
  <form id="filtersForm" class="row g-3 mb-4" method="GET">
    <div class="col-md-12">
      <label class="form-label d-block">{{ __('global.filter_origin') }}</label>

      <div class="form-check form-check-inline">
        <input
          class="form-check-input"
          type="checkbox"
          id="filterStore"
          name="origins[]"
          value="store"
          {{ in_array('store', $origins, true) ? 'checked' : '' }}
        >
        <label class="form-check-label" for="filterStore">{{ __('global.origin_store') }}</label>
      </div>

      <div class="form-check form-check-inline">
        <input
          class="form-check-input"
          type="checkbox"
          id="filterExternal"
          name="origins[]"
          value="external"
          {{ in_array('external', $origins, true) ? 'checked' : '' }}
        >
        <label class="form-check-label" for="filterExternal">{{ __('global.origin_external') }}</label>
      </div>
    </div>
  </form>

  <!-- Tabela -->
  <div class="table-responsive">
    <table class="table table-striped align-middle" id="installmentsTable">
      <thead class="table-dark">
        <tr>
          <th>{{ __('global.customer') }}</th>
          <th>{{ __('global.sale_number') }}</th>
          <th>{{ __('global.amount') }}</th>
          <th>{{ __('global.due_date') }}</th>
          <th>{{ __('global.status') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($installments as $i)
          <tr class="@switch($i->highlight)
              @case(\App\Http\Services\InstallmentScheduleService::OVERDUE) table-danger @break
              @case(\App\Http\Services\InstallmentScheduleService::TODAY)   table-warning @break
              @case(\App\Http\Services\InstallmentScheduleService::SOON_3)  table-rose @break
              @case(\App\Http\Services\InstallmentScheduleService::SOON_5)  table-rose-soft @break
              @default ''
          @endswitch">
            <td>{{ $i->sale->customer->name ?? '-' }}</td>
            <td>{{ $i->sale->id }}</td>
            <td>R$ {{ number_format($i->amount, 2, ',', '.') }}</td>
            <td>{{ \Carbon\Carbon::parse($i->due_date)->format('d/m/Y') }}</td>
            <td>{{ __($i->status) }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted">{{ __('global.no_results') }}</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Paginação + resumo em PT-BR -->
  <div class="mt-3" id="paginationWrap">
    @if ($installments->count())
    <p class="text-muted small mb-1">
        Exibindo {{ $installments->firstItem() }} a {{ $installments->lastItem() }}
        de {{ $installments->total() }} resultados
    </p>
    @endif

    {{-- Somente os links de paginação, sem o texto padrão em inglês --}}
    {{ $installments->onEachSide(1)->links('vendor.pagination.bootstrap-5-nosummary') }}
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(jq){
  // ready seguro mesmo com noConflict/defer
  function ready(fn){
    if (jq) { jq(fn); return; }
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function(){
    var $ = jq || window.jQuery;

    var $store    = $('#filterStore');
    var $external = $('#filterExternal');
    var $tbody    = $('#installmentsTable tbody');
    var $pagi     = $('#paginationWrap');

    function buildQueryKeepingOthers(selected){
      // selected: ['store'] | ['external'] | ['store','external']
      var params = new URLSearchParams(window.location.search);

      // Remover paginação e qualquer forma anterior de origins
      params.delete('page');
      var fresh = new URLSearchParams();
      params.forEach(function(v,k){
        if (k !== 'origins' && k !== 'origins[]') {
          fresh.append(k, v);
        }
      });

      // IMPORTANTÍSSIMO: usar 'origins[]' para múltiplos valores
      selected.forEach(function(origin){
        fresh.append('origins[]', origin);
      });

      return fresh.toString();
    }

    function applyFilters(){
      var isStore    = $store.is(':checked');
      var isExternal = $external.is(':checked');

      // 0 marcados -> não bate no backend; limpa no front
      if (!isStore && !isExternal) {
        $tbody.html('<tr><td colspan="5" class="text-center text-muted">{{ __('global.no_results') }}</td></tr>');
        $pagi.hide();
        return;
      }

      var selected = [];
      if (isStore)    selected.push('store');
      if (isExternal) selected.push('external');

      var qs   = buildQueryKeepingOthers(selected);
      var href = qs ? (window.location.pathname + '?' + qs) : window.location.pathname;

      window.location.assign(href);
    }

    $store.on('change', applyFilters);
    $external.on('change', applyFilters);

    // Estado inicial da UI se, por algum motivo, chegou com ambos desmarcados
    if (!$store.is(':checked') && !$external.is(':checked')) {
      $tbody.html('<tr><td colspan="5" class="text-center text-muted">{{ __('global.no_results') }}</td></tr>');
      $pagi.hide();
    } else {
      $pagi.show();
    }
  });
})(window.jQuery || window.$);
</script>
@endpush
