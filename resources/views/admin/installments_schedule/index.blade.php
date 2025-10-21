@extends('layouts.admin')

@section('content')

@php
    $origin = $filters['origin'] ?? 'all';

    // Inclui 'today'
    $c = $counts ?? ['today'=>0,'overdue'=>0,'upto3'=>0,'upto5'=>0,'normal'=>0];
    $s = $sums   ?? ['today'=>0,'overdue'=>0,'upto3'=>0,'upto5'=>0,'normal'=>0];

    function money_fmt($v) {
        return number_format((float)$v, 2, ',', '.');
    }

    // Mapeamento para classes Bootstrap válidas
    function row_class(?string $h): string {
        return match ($h) {
            'overdue' => 'table-danger',
            'today'   => 'table-warning',  // hoje
            'soon3'   => 'table-warning',  // até 3 dias
            'soon5'   => 'table-warning',  // até 5 dias
            default   => '',
        };
    }
@endphp

<div class="container-xxl">

  {{-- Título principal --}}
  <h2 class="mb-4 fw-bold">@lang('global.installments_schedule')</h2>

  {{-- Filtros + Contadores (mesmo nível) --}}
  <form id="filtersForm" method="GET" action="{{ route('admin.installments-schedule.index') }}" class="mb-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">

      {{-- Grupo de origem: Todos / Loja / Externo --}}
      <div class="btn-group" role="group" aria-label="@lang('global.origin_filter_aria')">
        <input type="radio" class="btn-check" name="origin" id="origin_all" value="all" autocomplete="off" {{ $origin==='all' ? 'checked' : '' }}>
        <label class="btn btn-outline-secondary" for="origin_all">@lang('global.all_results')</label>

        <input type="radio" class="btn-check" name="origin" id="origin_store" value="store" autocomplete="off" {{ $origin==='store' ? 'checked' : '' }}>
        <label class="btn btn-outline-secondary" for="origin_store">@lang('global.only_store')</label>

        <input type="radio" class="btn-check" name="origin" id="origin_external" value="external" autocomplete="off" {{ $origin==='external' ? 'checked' : '' }}>
        <label class="btn btn-outline-secondary" for="origin_external">@lang('global.only_external')</label>
      </div>

      {{-- Contadores estáticos do conjunto filtrado atual --}}
      <div class="d-flex flex-wrap align-items-center gap-2" id="staticCountsBar" aria-label="@lang('global.counts_aria')">

        {{-- NOVO: Vence hoje (laranja) --}}
        <span class="badge" style="background-color:#FFA500; color:#111;">
          @lang('global.due_today'):
          <span class="ms-1 fw-semibold" id="cnt_today">
            {{ $c['today'] ?? 0 }} (R$ {{ money_fmt($s['today'] ?? 0) }})
          </span>
        </span>

        <span class="badge text-bg-danger">
          @lang('global.total_overdue'):
          <span class="ms-1 fw-semibold" id="cnt_overdue">
            {{ $c['overdue'] ?? 0 }} (R$ {{ money_fmt($s['overdue'] ?? 0) }})
          </span>
        </span>

        <span class="badge text-bg-warning">
          @lang('global.total_upto3'):
          <span class="ms-1 fw-semibold" id="cnt_upto3">
            {{ $c['upto3'] ?? 0 }} (R$ {{ money_fmt($s['upto3'] ?? 0) }})
          </span>
        </span>

        <span class="badge text-bg-warning-subtle border text-dark">
          @lang('global.total_upto5'):
          <span class="ms-1 fw-semibold" id="cnt_upto5">
            {{ $c['upto5'] ?? 0 }} (R$ {{ money_fmt($s['upto5'] ?? 0) }})
          </span>
        </span>

        <span class="badge text-bg-secondary">
          @lang('global.total_normal'):
          <span class="ms-1 fw-semibold" id="cnt_normal">
            {{ $c['normal'] ?? 0 }} (R$ {{ money_fmt($s['normal'] ?? 0) }})
          </span>
        </span>
      </div>
    </div>
  </form>

  {{-- Tabela --}}
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
            // Se você trouxe "remaining" do SQL, use $i->remaining. Caso contrário, calcule:
            $remaining = isset($i->remaining)
                ? (float)$i->remaining
                : max(0, (float)($i->amount ?? 0) - (float)($i->paid_total ?? 0));

            $rowClass = row_class($i->highlight ?? null);
          @endphp
          <tr class="{{ $rowClass }}">
            <td>{{ $i->sale->customer->name ?? '-' }}</td>
            <td>{{ $i->sale->number ?? '-' }}</td>
            <td>{{ number_format($remaining, 2, ',', '.') }}</td>
            <td>{{ optional($i->due_date)->format('d/m/Y') }}</td>
            <td>
              @switch($i->highlight ?? '')
                @case('overdue') @lang('global.overdue') @break
                @case('today')   @lang('global.due_today') @break
                @case('soon3')   @lang('global.due_in_3_days') @break
                @case('soon5')   @lang('global.due_in_5_days') @break
                @default         @lang('global.normal')
              @endswitch
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted">@lang('global.no_results')</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Paginação --}}
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
  if (!form) return;

  // Troca de filtro -> reseta página e envia
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

  // Mantém o filtro ativo na paginação
  const originInputs = form.querySelectorAll('input[name="origin"]');
  let originValue = 'all';
  originInputs.forEach(i => { if (i.checked) originValue = i.value; });

  document.querySelectorAll('#paginationWrap a.page-link').forEach(a => {
    try {
      const url = new URL(a.href);
      if (!url.searchParams.has('origin')) {
        url.searchParams.set('origin', originValue);
        a.href = url.toString();
      }
    } catch (_) {}
  });
})();
</script>
@endpush
