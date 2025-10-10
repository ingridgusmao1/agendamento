@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.admin')
@section('title', __('global.products'))

@section('content')
<div class="d-flex mb-3">
  <div class="d-flex pm-form flex-grow-1">
    <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control pm-input me-2"
           placeholder="{{ __('global.search_product_placeholder') }}" autocomplete="off" />
  </div>
  <button class="btn pm-btn pm-btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalCreate">
    <i class="bi bi-plus-lg"></i> {{ __('global.new') }}
  </button>
</div>

<div class="card shadow-sm pm-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0 pm-table">
      <thead class="table-light">
        <tr>
          <th></th>
          <th>{{ __('global.name') }}</th>
          <th>{{ __('global.model') }}</th>
          <th>{{ __('global.stock_total') }}</th>
          <th>{{ __('global.size') }}</th>
          <th class="text-end">{{ __('global.price') }}</th>
          <th class="text-center">{{ __('global.actions') }}</th>
        </tr>
      </thead>
      <tbody id="products-tbody">
        {{-- carregado via AJAX (admin.products.fetch -> view admin.products._rows) --}}
      </tbody>
    </table>
  </div>

  <div class="pm-card-footer d-flex align-items-center justify-content-between px-3 py-2">
    <div id="products-range" class="text-muted small"><!-- 1–4 / 0 --></div>
    <div>
      <button id="btnPrevP" class="btn pm-btn pm-btn-outline-secondary btn-sm me-2" disabled>
        &larr; {{ __('global.prev') }}
      </button>
      <button id="btnNextP" class="btn pm-btn pm-btn-outline-secondary btn-sm" disabled>
        {{ __('global.next') }} &rarr;
      </button>
    </div>
  </div>
</div>

{{-- Modal Criar Produto --}}
<div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content pm-form" method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">{{ __('global.new_product') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">{{ __('global.name') }}</label>
            <input name="name" class="form-control pm-input" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('global.model') }}</label>
            <input name="model" class="form-control pm-input">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('global.stock_total') }}</label>
            <input type="number" name="stock_total" class="form-control pm-input" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('global.size') }}</label>
            <input name="size" class="form-control pm-input">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('global.price') }}</label>
            <input type="number" step="0.01" name="price" class="form-control pm-input" required>
          </div>
          <div class="col-12">
            <label class="form-label">{{ __('global.notes') }}</label>
            <textarea name="notes" class="form-control pm-input" rows="3"></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">{{ __('global.photo') }}</label>
            <input name="photos[]" type="file" class="form-control pm-input" accept="image/*" multiple>
            <small class="text-muted">{{ __('global.photo_caption') }}</small>
          </div>
          <div class="col-12">
            <label class="form-label">{{ __('global.complementary_info') }}</label>
            <textarea name="complements" class="form-control pm-input" rows="2"
              placeholder="{{ __('global.complements_hint') }}"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn pm-btn pm-btn-primary">{{ __('global.save') }}</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Editar Produto --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content pm-form" method="POST" id="formEditProduct" enctype="multipart/form-data">
      @csrf @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title">{{ __('global.edit_product') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">{{ __('global.name') }}</label>
            <input name="name" id="editName" class="form-control pm-input" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('global.model') }}</label>
            <input name="model" id="editModel" class="form-control pm-input">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('global.stock_total') }}</label>
            <input type="number" name="stock_total" id="editStockTotal" class="form-control pm-input" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('global.size') }}</label>
            <input name="size" id="editSize" class="form-control pm-input">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('global.price') }}</label>
            <input type="number" step="0.01" name="price" id="editPrice" class="form-control pm-input" required>
          </div>
          <div class="col-12">
            <label class="form-label">{{ __('global.notes') }}</label>
            <textarea name="notes" id="editNotes" class="form-control pm-input" rows="3"></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">{{ __('global.photo') }}</label>
            <input name="photos[]" type="file" class="form-control pm-input" accept="image/*" multiple>
            <small class="text-muted">{{ __('global.photo_caption') }}</small>
          </div>
          <div class="col-12">
            <label class="form-label">{{ __('global.complementary_info') }}</label>
            <textarea name="complements" id="editComplements" class="form-control pm-input" rows="2"
              placeholder="{{ __('global.complements_hint') }}"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn pm-btn pm-btn-primary">{{ __('global.save') }}</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('click', function (ev) {
  const btn = ev.target.closest('.btn-edit-product');
  if (!btn) return;

  const modalEl = document.getElementById('modalEdit');
  if (!modalEl) return;

  const formEl          = modalEl.querySelector('#formEditProduct');
  const inputName       = modalEl.querySelector('#editName');
  const inputModel      = modalEl.querySelector('#editModel');
  const inputSize       = modalEl.querySelector('#editSize');
  const inputPrice      = modalEl.querySelector('#editPrice');
  const inputStockTotal = modalEl.querySelector('#editStockTotal');
  const textareaNotes   = modalEl.querySelector('#editNotes');
  const textareaComp    = modalEl.querySelector('#editComplements');

  formEl.setAttribute('action', btn.dataset.updateUrl);

  inputName && (inputName.value       = btn.dataset.name ?? '');
  inputModel && (inputModel.value     = btn.dataset.model ?? '');
  inputSize && (inputSize.value       = btn.dataset.size ?? '');
  inputPrice && (inputPrice.value     = btn.dataset.price ?? '');
  textareaNotes && (textareaNotes.value = btn.dataset.notes ?? '');
  textareaComp && (textareaComp.value   = btn.dataset.complements ?? '');

  // Lê tanto data-stock_total quanto data-stock-total (suporta ambos)
  const stockFromBtn =
      (btn.dataset.stock_total !== undefined ? btn.dataset.stock_total : undefined) ??
      (btn.dataset.stockTotal !== undefined ? btn.dataset.stockTotal : '');

  if (inputStockTotal) inputStockTotal.value = stockFromBtn ?? '';

  // limpa input de fotos, se existir
  const photosInput = modalEl.querySelector('input[name="photos[]"]');
  if (photosInput) photosInput.value = '';
});

// Listagem com paginação/fetch
(function(){
  let page = 1;

  const $q        = document.querySelector('input[name="q"]');
  const $per      = document.querySelector('[name="per_page"]');
  const $tbody    = document.getElementById('products-tbody');
  const $btnPrev  = document.getElementById('btnPrevP');
  const $btnNext  = document.getElementById('btnNextP');
  const $range    = document.getElementById('products-range');
  const urlFetch  = @json(route('admin.products.fetch'));
  const confirmDeleteMsg = @json(__('global.exclude_this_product'));

  function currentPerPage(){
    const v = $per ? parseInt($per.value, 10) : 20;
    return Number.isFinite(v) ? Math.max(5, Math.min(100, v)) : 20;
  }

  function initTooltips(){
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
  }

  function wireDeleteConfirm(){
    document.querySelectorAll('.delete-form').forEach(f => {
      f.addEventListener('submit', (e) => {
        if (!confirm(confirmDeleteMsg)) e.preventDefault();
      });
    });
  }

  function updateRange(meta){
    const per = meta.per_page ?? meta.perPage ?? currentPerPage();
    const start = meta.total === 0 ? 0 : ((meta.page - 1) * per + 1);
    const end   = Math.min(meta.page * per, meta.total);
    if ($range) $range.textContent = `${start}–${end} / ${meta.total}`;
  }

  function updatePager(meta){
    const hasPrev = ('hasPrev' in meta) ? !!meta.hasPrev : (meta.page > 1);
    const hasNext = ('hasNext' in meta) ? !!meta.hasNext : !!meta.hasMore;
    if ($btnPrev) $btnPrev.disabled = !hasPrev;
    if ($btnNext) $btnNext.disabled = !hasNext;
  }

  function safeRender(meta){
    if ($tbody) $tbody.innerHTML = meta.html || '';
    updatePager(meta);
    updateRange(meta);
    if (typeof window.initTooltips !== 'function') {
      window.initTooltips = function(){};
    }
    window.initTooltips();
    wireDeleteConfirm();
  }

  function load(){
    const params = new URLSearchParams({
      q: $q?.value || '',
      page: String(page),
      per_page: String(currentPerPage()),
    });
    fetch(`${urlFetch}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(async r => {
        const ok = r.ok;
        const data = await r.json().catch(() => null);
        if (!ok || !data) {
          if ($tbody) $tbody.innerHTML = '';
          updatePager({ page, hasNext:false, hasPrev:false });
          if ($range) $range.textContent = '—';
          return;
        }
        safeRender(data);
      })
      .catch(() => {
        if ($tbody) $tbody.innerHTML = '';
        updatePager({ page, hasNext:false, hasPrev:false });
        if ($range) $range.textContent = '—';
      });
  }

  $btnPrev?.addEventListener('click', function(){ if (page > 1){ page--; load(); } });
  $btnNext?.addEventListener('click', function(){ page++; load(); });

  let t;
  $q?.addEventListener('input', function(){
    page = 1;
    clearTimeout(t);
    t = setTimeout(load, 300);
  });

  $per?.addEventListener('change', function(){
    page = 1;
    load();
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', load);
  } else {
    load();
  }
})();
</script>
@endpush
