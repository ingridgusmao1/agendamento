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
          <th>{{ __('global.name') }}</th>
          <th>{{ __('global.model') }}</th>
          <th>{{ __('global.color') }}</th>
          <th>{{ __('global.size') }}</th>
          <th class="text-end">{{ __('global.price') }}</th>
          <th class="text-end">{{ __('global.actions') }}</th>
        </tr>
      </thead>
      <tbody id="products-tbody">
        {{-- carregado via AJAX --}}
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
    <form class="modal-content pm-form" method="POST" action="{{ route('admin.products.store') }}">
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
            <label class="form-label">{{ __('global.color') }}</label>
            <input name="color" class="form-control pm-input">
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
            <input name="photo_path" class="form-control pm-input">
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
    <form class="modal-content pm-form" method="POST" id="formEditProduct">
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
            <label class="form-label">{{ __('global.color') }}</label>
            <input name="color" id="editColor" class="form-control pm-input">
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
            <input name="photo_path" id="editPhoto" class="form-control pm-input">
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
(function(){
  let page = 1;

  const $q        = document.querySelector('input[name="q"]');
  const $tbody    = document.getElementById('products-tbody');
  const $btnPrev  = document.getElementById('btnPrevP');
  const $btnNext  = document.getElementById('btnNextP');
  const $range    = document.getElementById('products-range');
  const urlFetch  = @json(route('admin.products.fetch'));
  const confirmDeleteMsg = @json(__('global.exclude_this_product'));

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
    const start = meta.total === 0 ? 0 : ((meta.page - 1) * meta.perPage + 1);
    const end   = Math.min(meta.page * meta.perPage, meta.total);
    $range.textContent = `${start}–${end} / ${meta.total}`;
  }

  function load(){
    const params = new URLSearchParams({ q: $q.value || '', page: String(page) });
    fetch(`${urlFetch}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(meta => {
        $tbody.innerHTML  = meta.html;
        $btnPrev.disabled = !meta.hasPrev;
        $btnNext.disabled = !meta.hasNext;
        updateRange(meta);
        initTooltips();
        wireDeleteConfirm();
      })
      .catch(console.error);
  }

  $btnPrev?.addEventListener('click', function(){ if (page > 1){ page--; load(); } });
  $btnNext?.addEventListener('click', function(){ page++; load(); });

  let t;
  $q?.addEventListener('input', function(){
    page = 1;
    clearTimeout(t);
    t = setTimeout(load, 300);
  });

  // Abrir modal Editar (preenche com data-attrs da linha)
  document.addEventListener('click', function(e){
    const btnEdit = e.target.closest('.btn-edit-product');
    if (!btnEdit) return;

    const id    = btnEdit.getAttribute('data-id');
    document.getElementById('editName').value        = btnEdit.getAttribute('data-name')  ?? '';
    document.getElementById('editModel').value       = btnEdit.getAttribute('data-model') ?? '';
    document.getElementById('editColor').value       = btnEdit.getAttribute('data-color') ?? '';
    document.getElementById('editSize').value        = btnEdit.getAttribute('data-size')  ?? '';
    document.getElementById('editPrice').value       = btnEdit.getAttribute('data-price') ?? '';
    document.getElementById('editNotes').value       = btnEdit.getAttribute('data-notes') ?? '';
    document.getElementById('editPhoto').value       = btnEdit.getAttribute('data-photo') ?? '';
    document.getElementById('editComplements').value = btnEdit.getAttribute('data-complements') ?? '';

    document.getElementById('formEditProduct').setAttribute('action', '/admin/products/' + id);

    new bootstrap.Modal(document.getElementById('modalEdit')).show();
  });

  document.addEventListener('DOMContentLoaded', load);
})();
</script>
@endpush
