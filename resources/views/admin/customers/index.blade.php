@extends('layouts.admin')
@section('title', __('global.customers'))

@section('content')
@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
<div class="d-flex mb-3">
  <div class="d-flex pm-form flex-grow-1">
    <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control pm-input me-2"
           placeholder="{{ __('global.search_customer_placeholder') }}" autocomplete="off" />
  </div>
  <button class="btn pm-btn pm-btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalCreate">
    <i class="bi bi-plus-lg"></i> {{ __('global.new') }}
  </button>
</div>

<div class="card shadow-sm pm-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th style="width:120px">{{ __('global.avatar') }}</th>
          <th>{{ __('global.name') }}</th>
          <th>{{ __('global.city') }}</th>
          <th>{{ __('global.district') }}</th>
          <th>{{ __('global.cpf') }}</th>
          <th>{{ __('global.phone') }}</th>
          <th class="text-end" style="width:140px">{{ __('global.actions') }}</th>
        </tr>
      </thead>
      <tbody id="tbodyRows"></tbody>
    </table>
  </div>

  <div class="p-2 border-top d-flex align-items-center gap-2">
    <div class="ms-auto d-flex align-items-center gap-2">
      <label class="text-muted small">{{ __('global.per_page') }}</label>
      <select id="perPage" class="form-select form-select-sm" style="width:auto">
        <option>15</option><option>25</option><option>50</option><option>100</option>
      </select>
      <span id="range" class="text-muted small">0–0 / 0</span>
      <button id="btnPrevP" class="btn pm-btn pm-btn-outline-secondary btn-sm" disabled>
        &larr; {{ __('global.prev') }}
      </button>
      <button id="btnNextP" class="btn pm-btn pm-btn-outline-secondary btn-sm" disabled>
        {{ __('global.next') }} &rarr;
      </button>
    </div>
  </div>
</div>

{{-- Modal Criar Cliente --}}
<div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content pm-form" method="POST" action="{{ route('admin.customers.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">{{ __('global.new_customer') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('global.close') }}"></button>
      </div>
      <div class="modal-body">
        @include('admin.customers._form_fields')
      </div>
      <div class="modal-footer">
        <button class="btn pm-btn pm-btn-primary">{{ __('global.save') }}</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Editar Cliente --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="formEdit" class="modal-content pm-form" method="POST" enctype="multipart/form-data">
      @csrf @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title">{{ __('global.edit_customer') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('global.close') }}"></button>
      </div>
      <div class="modal-body">
        @include('admin.customers._form_fields')
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
  const $q     = document.querySelector('input[name="q"]');
  const $tbody = document.getElementById('tbodyRows');
  const $per   = document.getElementById('perPage');
  const $btnPrev = document.getElementById('btnPrevP');
  const $btnNext = document.getElementById('btnNextP');
  const $range   = document.getElementById('range');

  let page = 1;

  async function load(){
    const params = new URLSearchParams();
    if ($q.value) params.set('q', $q.value);
    params.set('page', page);
    params.set('per_page', $per.value);

    const url = "{{ route('admin.customers.fetch') }}" + '?' + params.toString();
    const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const meta = await resp.json();

    if ($tbody) $tbody.innerHTML = meta.html || '';
    updatePager(meta);
    updateRange(meta);

    initEditButtons();
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

  $q.addEventListener('input', () => { page = 1; load(); });
  $per.addEventListener('change', () => { page = 1; load(); });
  $btnPrev.addEventListener('click', () => { if (page > 1){ page--; load(); } });
  $btnNext.addEventListener('click', () => { page++; load(); });

  function initEditButtons(){
    document.querySelectorAll('.btn-edit-customer').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const action = "{{ route('admin.customers.update', ':id') }}".replace(':id', id);
        const $form = document.getElementById('formEdit');
        $form.setAttribute('action', action);

        // Preenche campos
        const set = (name, val) => {
          const el = $form.querySelector(`[name="${name}"]`);
          if (el) el.value = val ?? '';
        };

        set('name',            btn.dataset.name);
        set('street',          btn.dataset.street);
        set('number',          btn.dataset.number);
        set('district',        btn.dataset.district);
        set('city',            btn.dataset.city);
        set('reference_point', btn.dataset.reference_point);
        set('rg',              btn.dataset.rg);
        set('cpf',             btn.dataset.cpf);
        set('phone',           btn.dataset.phone);
        set('other_contact',   btn.dataset.other_contact);
        set('lat',             btn.dataset.lat);
        set('lng',             btn.dataset.lng);

        // Avatar exibido (se houver)
        const $preview = $form.querySelector('[data-avatar-preview]');
        if ($preview){
          const src = btn.dataset.avatar ? btn.dataset.avatar : '';
          $preview.src = src || 'https://via.placeholder.com/240?text=Avatar';
        }

        new bootstrap.Modal(document.getElementById('modalEdit')).show();
      });
    });
  }

  document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', load)
    : load();
})();
</script>
@endpush
