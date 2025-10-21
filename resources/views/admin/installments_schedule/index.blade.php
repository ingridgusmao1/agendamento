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
            <td>
              @php
                $sale = $i->sale;
                $cust = $sale?->customer;
                // Texto a exibir no link: número da venda, senão collection_note, senão '-'
                $saleText = $sale?->number ?: ($sale?->collection_note ?: '-');
              @endphp

              <a href="#"
                class="link-sale-modal"
                data-bs-toggle="modal"
                data-bs-target="#saleInfoModal"
                data-sale-number="{{ e($sale?->number ?? '') }}"
                data-collection-note="{{ e($sale?->collection_note ?? '') }}"
                data-cust-name="{{ e($cust?->name ?? '') }}"
                data-street="{{ e($cust?->street ?? '') }}"
                data-number="{{ e($cust?->number ?? '') }}"
                data-district="{{ e($cust?->district ?? '') }}"
                data-city="{{ e($cust?->city ?? '') }}"
                data-reference-point="{{ e($cust?->reference_point ?? '') }}"
                data-phone="{{ e($cust?->phone ?? '') }}"
                data-gps-lat="{{ $sale?->gps_lat !== null ? (string)$sale->gps_lat : '' }}"
                data-gps-lng="{{ $sale?->gps_lng !== null ? (string)$sale->gps_lng : '' }}"
              >
                {{ $saleText }}
              </a>
            </td>
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

  {{-- Modal: Informações da venda/cliente --}}
  <div class="modal fade" id="saleInfoModal" tabindex="-1" aria-labelledby="saleInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="saleInfoModalLabel">@lang('global.sale_details')</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('global.close')"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <h6 class="mb-2">@lang('global.customer_address')</h6>
              <ul class="list-unstyled mb-0" id="custAddress">
                <li><strong>@lang('global.customer'):</strong> <span id="m_cust_name">-</span></li>
                <li><strong>@lang('global.street'):</strong> <span id="m_street">-</span></li>
                <li><strong>@lang('global.number'):</strong> <span id="m_number">-</span></li>
                <li><strong>@lang('global.district'):</strong> <span id="m_district">-</span></li>
                <li><strong>@lang('global.city'):</strong> <span id="m_city">-</span></li>
                <li><strong>@lang('global.reference_point'):</strong> <span id="m_reference_point">-</span></li>
                <li><strong>@lang('global.phone'):</strong> <span id="m_phone">-</span></li>
              </ul>
            </div>

            <div class="col-md-6">
              <h6 class="mb-2">@lang('global.sale')</h6>
              <ul class="list-unstyled mb-0">
                <li><strong>@lang('global.sale_number'):</strong> <span id="m_sale_number">-</span></li>
                <li><strong>@lang('global.collection_note'):</strong> <span id="m_collection_note">-</span></li>
              </ul>

              {{-- Bloco do Google Maps: renderiza só se houver lat/lng --}}
              <div id="m_maps_block" class="mt-3" style="display:none;">
                <a id="m_maps_link" href="#" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                  @lang('global.open_in_google_maps')
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('global.close')</button>
        </div>

      </div>
    </div>
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
(() => {
  // ---------------------------
  // 1) Filtro por origem (form)
  // ---------------------------
  const form = document.getElementById('filtersForm');
  if (form) {
    form.addEventListener('change', (e) => {
      if (e.target && e.target.name === 'origin') {
        // Reset de página ao trocar origem
        form.querySelectorAll('input[name="page"]').forEach(n => n.remove());
        const p = document.createElement('input');
        p.type  = 'hidden';
        p.name  = 'page';
        p.value = '1';
        form.appendChild(p);
        form.submit();
      }
    });

    // ---------------------------
    // 2) Preserva origem na paginação
    // ---------------------------
    let originValue = 'all';
    form.querySelectorAll('input[name="origin"]').forEach(i => { if (i.checked) originValue = i.value; });

    document.querySelectorAll('#paginationWrap a.page-link').forEach(a => {
      try {
        const url = new URL(a.href);
        if (!url.searchParams.has('origin')) {
          url.searchParams.set('origin', originValue);
          a.href = url.toString();
        }
      } catch (_) { /* ignora urls inválidas */ }
    });
  }

  // ---------------------------
  // 3) Modal de detalhes da venda/cliente
  // ---------------------------
  const modal = document.getElementById('saleInfoModal');
  if (modal) {
    modal.addEventListener('show.bs.modal', (event) => {
      const trigger = event.relatedTarget;
      if (!trigger) return;

      const d = trigger.dataset;
      const $ = (sel) => modal.querySelector(sel);

      // Text helpers (evita "undefined" na UI)
      const txt = (v, fallback='-') => (v && String(v).trim() !== '' ? String(v) : fallback);

      // Preenche venda
      $('#m_sale_number').textContent     = txt(d.saleNumber);
      $('#m_collection_note').textContent = txt(d.collectionNote);

      // Preenche cliente/endereço
      $('#m_cust_name').textContent       = txt(d.custName);
      $('#m_street').textContent          = txt(d.street);
      $('#m_number').textContent          = txt(d.number);
      $('#m_district').textContent        = txt(d.district);
      $('#m_city').textContent            = txt(d.city);
      $('#m_reference_point').textContent = txt(d.referencePoint);
      $('#m_phone').textContent           = txt(d.phone);

      // Google Maps (apenas se houver lat/lng válidos)
      const lat = (d.gpsLat || '').trim();
      const lng = (d.gpsLng || '').trim();
      const mapsBlock = $('#m_maps_block');
      const mapsLink  = $('#m_maps_link');

      const hasCoords = lat !== '' && lng !== '' && !Number.isNaN(Number(lat)) && !Number.isNaN(Number(lng));
      if (hasCoords) {
        const url = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(lat)},${encodeURIComponent(lng)}`;
        mapsLink.setAttribute('href', url);
        mapsBlock.style.display = '';
      } else {
        mapsLink.removeAttribute('href');
        mapsBlock.style.display = 'none';
      }

      // Título dinâmico (opcional)
      const title = $('#saleInfoModalLabel');
      if (title) title.textContent = `@lang('global.sale_details') — ${txt(d.saleNumber)}`;
    });
  }
})();
</script>
@endpush
