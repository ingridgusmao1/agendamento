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
          <th style="width:160px"></th>

          <th>
            <button type="button" class="btn btn-link p-0 sortable" data-sort="name">
              {{ __('global.name') }}
              <i class="bi bi-chevron-expand sort-icon"></i>
            </button>
          </th>

          <th>
            <button type="button" class="btn btn-link p-0 sortable" data-sort="model">
              {{ __('global.model') }}
              <i class="bi bi-chevron-expand sort-icon"></i>
            </button>
          </th>

          <th>
            <button type="button" class="btn btn-link p-0 sortable" data-sort="stock_total">
              {{ __('global.stock_total') }}
              <i class="bi bi-chevron-expand sort-icon"></i>
            </button>
          </th>

          <th>
            <button type="button" class="btn btn-link p-0 sortable" data-sort="size">
              {{ __('global.size') }}
              <i class="bi bi-chevron-expand sort-icon"></i>
            </button>
          </th>

          <th class="text-end">
            <button type="button" class="btn btn-link p-0 sortable" data-sort="price">
              {{ __('global.price') }}
              <i class="bi bi-chevron-expand sort-icon"></i>
            </button>
          </th>

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
(function () {
  // ---------------------------
  // Estado
  // ---------------------------
  let page  = 1;
  let sort  = 'stock_total'; // padrão: menor estoque primeiro
  let order = 'asc';

  // ---------------------------
  // Referências de UI
  // ---------------------------
  const $q       = document.querySelector('input[name="q"]');
  const $per     = document.querySelector('[name="per_page"]');
  const $tbody   = document.getElementById('products-tbody');
  const $btnPrev = document.getElementById('btnPrevP');
  const $btnNext = document.getElementById('btnNextP');
  const $range   = document.getElementById('products-range');

  const urlFetch = @json(route('admin.products.fetch'));
  const confirmDeleteMsg = @json(__('global.exclude_this_product'));

  // ---------------------------
  // Ordenação (UI)
  // ---------------------------
  const $sortableBtns = document.querySelectorAll('.sortable');

  function clearSortIcons() {
    document.querySelectorAll('.sortable .sort-icon').forEach(icon => {
      icon.classList.remove('bi-chevron-up', 'bi-chevron-down');
      if (!icon.classList.contains('bi-chevron-expand')) {
        icon.classList.add('bi-chevron-expand');
      }
    });
  }

  function applySortIcon(activeBtn) {
    clearSortIcons();
    const icon = activeBtn.querySelector('.sort-icon');
    if (!icon) return;
    icon.classList.remove('bi-chevron-expand');
    icon.classList.add(order === 'asc' ? 'bi-chevron-up' : 'bi-chevron-down');
  }

  $sortableBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const chosen = this.getAttribute('data-sort');
      if (!chosen) return;

      if (sort === chosen) {
        order = (order === 'asc') ? 'desc' : 'asc';
      } else {
        sort  = chosen;
        order = 'asc'; // direção padrão ao trocar de coluna
      }
      applySortIcon(this);
      page = 1;
      load();
    });
  });

  // ---------------------------
  // Utilidades
  // ---------------------------
  function currentPerPage(){
    const v = $per ? parseInt($per.value, 10) : 20;
    return Number.isFinite(v) ? Math.max(5, Math.min(100, v)) : 20;
  }

  function wireDeleteConfirm(){
    document.querySelectorAll('.delete-form').forEach(f => {
      if (f.dataset._wired) return;
      f.dataset._wired = '1';
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

    // tooltips e confirm
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    wireDeleteConfirm();
  }

  function load(){
    const params = new URLSearchParams({
      q: $q?.value || '',
      page: String(page),
      per_page: String(currentPerPage()),
      sort: sort,
      order: order
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

  // ---------------------------
  // Modal de Edição (preenchimento robusto)
  // ---------------------------
  (function bindEditModal(){
    const modalEl = document.getElementById('modalEdit');
    if (!modalEl) return;

    modalEl.addEventListener('show.bs.modal', function (ev) {
      const btn = ev.relatedTarget; // botão que abriu o modal
      if (!btn) return;

      const formEl          = modalEl.querySelector('#formEditProduct');
      const inputName       = modalEl.querySelector('#editName');
      const inputModel      = modalEl.querySelector('#editModel');
      const inputSize       = modalEl.querySelector('#editSize');
      const inputPrice      = modalEl.querySelector('#editPrice');
      const inputStockTotal = modalEl.querySelector('#editStockTotal');
      const textareaNotes   = modalEl.querySelector('#editNotes');
      const textareaComp    = modalEl.querySelector('#editComplements');

      // action
      const updateUrl = btn.getAttribute('data-update-url') || '';
      if (formEl && updateUrl) formEl.setAttribute('action', updateUrl);

      // valores (sempre via getAttribute para evitar incompatibilidades)
      if (inputName)       inputName.value       = btn.getAttribute('data-name') ?? '';
      if (inputModel)      inputModel.value      = btn.getAttribute('data-model') ?? '';
      if (inputSize)       inputSize.value       = btn.getAttribute('data-size') ?? '';
      if (inputPrice)      inputPrice.value      = btn.getAttribute('data-price') ?? '';
      if (textareaNotes)   textareaNotes.value   = btn.getAttribute('data-notes') ?? '';
      if (textareaComp)    textareaComp.value    = btn.getAttribute('data-complements') ?? '';
      if (inputStockTotal) inputStockTotal.value = btn.getAttribute('data-stock-total') ?? '';

      // limpa input de fotos (se existir)
      const photosInput = modalEl.querySelector('input[name="photos[]"]');
      if (photosInput) photosInput.value = '';
    });
  })();

  // ---------------------------
  // Eventos de paginação / busca
  // ---------------------------
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

  // ícone do sort inicial
  (function initSortIconOnLoad() {
    const btn = document.querySelector(`.sortable[data-sort="${sort}"]`);
    if (btn) applySortIcon(btn);
  })();

  // carregamento inicial
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', load);
  } else {
    load();
  }
})();
</script>
@endpush
