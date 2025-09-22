@extends('layouts.admin')
@section('title', __('global.sales'))

@section('content')
@if (session('ok'))
  <div class="alert alert-success">{{ session('ok') }}</div>
@endif
@if (session('err'))
  <div class="alert alert-danger">{{ session('err') }}</div>
@endif

{{-- Filtros (busca + status) – sem paginação aqui em cima --}}
<div class="d-flex mb-3 align-items-center gap-2">
  <input type="text" name="q" class="form-control" placeholder="{{ __('global.search_sale_placeholder') }}" autocomplete="off">
  <select id="filterStatus" class="form-select" style="width:auto">
    <option value="">{{ __('global.all_status') }}</option>
    <option value="aberto">{{ __('global.open_sale_dropdown') }}</option>
    <option value="atrasado">{{ __('global.late_dropdown') }}</option>
    <option value="fechado">{{ __('global.closed_dropdown') }}</option>
  </select>
</div>

<div class="card shadow-sm pm-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th style="width:120px"></th>
          <th>{{ __('global.customer_name') }}</th>
          <th>{{ __('global.status') }}</th>
          <th class="text-end">{{ __('global.total') }}</th>
          <th style="width:160px">{{ __('global.register_date') }}</th>
          <th class="text-end" style="width:150px">{{ __('global.actions') }}</th>
        </tr>
      </thead>
      <tbody id="tbodyRows">
        <tr><td colspan="7" class="text-center text-muted">...</td></tr>
      </tbody>
    </table>
  </div>

  {{-- >>> Rodapé com Itens por página + Range + Prev/Next (movido para baixo) <<< --}}
  <div class="card-footer bg-white">
    <div class="row g-2 align-items-center">
      <div class="col-12 col-md-4">
        <label class="text-muted small">{{ __('global.per_page') }}</label>
        <select id="perPage" class="form-select form-select-sm w-auto d-inline-block ms-2" style="width:auto">
          <option>15</option><option>25</option><option>50</option><option>100</option>
        </select>
      </div>
      <div class="col-12 col-md-4 text-center">
        <span id="range" class="text-muted small">0–0 / 0</span>
      </div>
      <div class="col-12 col-md-4">
        <div class="d-flex justify-content-md-end gap-2">
          <button id="btnPrevP" class="btn pm-btn pm-btn-outline-secondary btn-sm" disabled>&larr; {{ __('global.previous') }}</button>
          <button id="btnNextP" class="btn pm-btn pm-btn-outline-secondary btn-sm" disabled>{{ __('global.next') }} &rarr;</button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal Detalhes (preenchido via AJAX) --}}
<div class="modal fade" id="modalDetails" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('global.sale_details') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('global.close') }}"></button>
      </div>
      <div class="modal-body" id="detailsBody"><!-- preenchido via AJAX --></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const $q      = document.querySelector('input[name="q"]');
  const $per    = document.getElementById('perPage');
  const $tbody  = document.getElementById('tbodyRows');
  const $btnPrev= document.getElementById('btnPrevP');
  const $btnNext= document.getElementById('btnNextP');
  const $range  = document.getElementById('range');
  const $status = document.getElementById('filterStatus');
  let page = 1;

  async function load(){
    const params = new URLSearchParams();
    if ($q.value) params.set('q', $q.value);
    if ($status.value) params.set('status', $status.value);
    params.set('page', page);
    params.set('per_page', $per.value);

    // mantém a rota original
    const url = "{{ route('admin.sales.fetch') }}?" + params.toString();
    const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const meta = await resp.json();

    $tbody.innerHTML = meta.html || '';
    updatePager(meta);
    updateRange(meta);
    bindRowButtons();
    initTooltips?.();
  }

  function updatePager(meta){
    const hasPrev = ('hasPrev' in meta) ? !!meta.hasPrev : (meta.page > 1);
    const hasNext = ('hasNext' in meta) ? !!meta.hasNext : !!meta.hasMore;
    $btnPrev.disabled = !hasPrev;
    $btnNext.disabled = !hasNext;
  }

  function updateRange(meta){
    const start = (meta.total === 0) ? 0 : ((meta.page - 1) * meta.perPage + 1);
    const end   = Math.min(meta.page * meta.perPage, meta.total);
    $range.textContent = `${start}–${end} / ${meta.total}`;
  }

  function bindRowButtons(){
    document.querySelectorAll('.btn-sale-details').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.dataset.id;
        const url = "{{ route('admin.sales.show', ':id') }}".replace(':id', id);
        const r = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        const data = await r.json();
        document.getElementById('detailsBody').innerHTML = data.html || '';
        new bootstrap.Modal(document.getElementById('modalDetails')).show();
      });
    });
  }

  $q.addEventListener('input',   () => { page = 1; load(); });
  $per.addEventListener('change',() => { page = 1; load(); });
  $status.addEventListener('change', () => { page = 1; load(); });
  $btnPrev.addEventListener('click', () => { if (page > 1){ page--; load(); } });
  $btnNext.addEventListener('click', () => { page++; load(); });

  document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', load)
    : load();
})();
</script>
@endpush
