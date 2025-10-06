@extends('layouts.admin')

@section('content')
@php
    $origin = $filters['origin'] ?? 'all';
@endphp

<div class="container-xxl">
  <form id="filtersForm" method="GET" action="{{ route('admin.installments-schedule.index') }}" class="mb-3">
    <div class="btn-group" role="group" aria-label="@lang('global.origin_filter_aria')">
      <input type="radio" class="btn-check" name="origin" id="origin_all" value="all" autocomplete="off" {{ $origin==='all' ? 'checked' : '' }}>
      <label class="btn btn-outline-secondary" for="origin_all">@lang('global.all_results')</label>

      <input type="radio" class="btn-check" name="origin" id="origin_store" value="store" autocomplete="off" {{ $origin==='store' ? 'checked' : '' }}>
      <label class="btn btn-outline-secondary" for="origin_store">@lang('global.only_store')</label>

      <input type="radio" class="btn-check" name="origin" id="origin_external" value="external" autocomplete="off" {{ $origin==='external' ? 'checked' : '' }}>
      <label class="btn btn-outline-secondary" for="origin_external">@lang('global.only_external')</label>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-installments align-middle">
      <thead class="table-dark">
        <tr>
          <th>@lang('global.customer')</th>
          <th>@lang('global.sale')</th>
          <th>@lang('global.amount')</th>
          <th>@lang('global.due_date')</th>
          <th>@lang('global.status')</th>
        </tr>
      </thead>
      <tbody>
        @forelse($installments as $i)
          @php
            $rowClass = match($i->highlight ?? '') {
              'overdue' => 'table-danger',          // Atrasado
              'today'   => 'table-warning-strong',  // Vence hoje
              'soon3'   => 'table-warning',         // ≤ 3 dias
              'soon5'   => 'table-warning-light',   // ≤ 5 dias
              default   => '',
            };
          @endphp
          <tr class="{{ $rowClass }}">
            <td>{{ $i->sale->customer->name ?? '-' }}</td>
            <td>{{ $i->sale->number ?? '-' }}</td>
            <td>{{ number_format($i->amount, 2, ',', '.') }}</td>
            <td>{{ optional($i->due_date)->format('d/m/Y') }}</td>
            <td>
              @switch($i->highlight)
                @case('overdue') @lang('global.overdue') @break
                @case('today')   @lang('global.due_today') @break
                @case('soon3')   @lang('global.due_in_3_days') @break
                @case('soon5')   @lang('global.due_in_5_days') @break
                @default         @lang('global.normal')
              @endswitch
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted">@lang('global.no_results')</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3" id="paginationWrap">
    @if ($installments->count())
      <p class="text-muted small mb-1">
        {{ __('global.showing_range', [
            'first' => $installments->firstItem(),
            'last'  => $installments->lastItem(),
            'total' => $installments->total()
        ]) }}
      </p>
    @endif

    {{ $installments->onEachSide(1)->links('vendor.pagination.bootstrap-5-nosummary') }}
  </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
  const form = document.getElementById('filtersForm');
  form.addEventListener('change', function(e) {
    if (e.target && e.target.name === 'origin') {
      Array.from(form.querySelectorAll('input[name="page"]')).forEach(n => n.remove());
      const p = document.createElement('input');
      p.type  = 'hidden';
      p.name  = 'page';
      p.value = '1';
      form.appendChild(p);
      form.submit();
    }
  });
})();
</script>
@endpush
