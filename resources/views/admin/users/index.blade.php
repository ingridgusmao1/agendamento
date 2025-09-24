@extends('layouts.admin')
@section('title', __('global.employees'))

@section('content')
<div class="d-flex mb-3">
  <div class="d-flex pm-form flex-grow-1">
    <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control pm-input me-2"
           placeholder="{{ __('global.search_by_name_or_code') }}" autocomplete="off" />
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
          <th>{{ __('global.code') }}</th>
          <th>{{ __('global.name') }}</th>
          <th>{{ __('global.type') }}</th>
          <th class="text-end">{{ __('global.actions') }}</th>
        </tr>
      </thead>
      <tbody id="users-tbody">
        {{-- carregado via AJAX --}}
      </tbody>
    </table>
  </div>

  <div class="pm-card-footer d-flex align-items-center justify-content-between px-3 py-2">
    <div id="users-range" class="text-muted small"><!-- 1–4 / 0 --></div>
    <div>
      <button id="btnPrev" class="btn pm-btn pm-btn-outline-secondary btn-sm me-2" disabled>
        &larr; {{ __('global.prev') }}
      </button>
      <button id="btnNext" class="btn pm-btn pm-btn-outline-secondary btn-sm" disabled>
        {{ __('global.next') }} &rarr;
      </button>
    </div>
  </div>
</div>

{{-- Modal Novo --}}
<div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content pm-form" method="POST" action="{{ route('admin.users.store') }}">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">{{ __('global.new_user') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">{{ __('global.code') }}</label>
          <input name="code" class="form-control pm-input" required>
        </div>
        <div class="mb-2">
          <label class="form-label">{{ __('global.name') }}</label>
          <input name="name" class="form-control pm-input" required>
        </div>
        <div class="mb-2">
          <label class="form-label">{{ __('global.type') }}</label>
          <select name="type" class="form-select pm-select" required>
              <option value="" disabled selected>{{ __('global.modal_choose') }}</option>
              @foreach($types as $t)
                  @if ($t == 'admin')
                      <option value="{{ $t }}">{{ __('global.modal_administrator') }}</option>
                  @elseif (in_array($t, ['cobrador', 'vendedor']))
                      <option value="{{ $t }}">{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                  @elseif ($t == 'vendedor_cobrador')
                      <option value="{{ $t }}">{{ __('global.modal_salesman_collector') }}</option>
                  @else
                      <option value="{{ $t }}">{{ $t }}</option>
                  @endif
              @endforeach
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">{{ __('global.password') }}</label>
          <input type="password" name="password" class="form-control pm-input" required minlength="4">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn pm-btn pm-btn-primary">{{ __('global.save') }}</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Editar --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content pm-form" method="POST" id="formEdit">
      @csrf @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title">{{ __('global.edit_user') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">{{ __('global.name') }}</label>
          <input name="name" id="editName" class="form-control pm-input" required>
        </div>
        <div class="mb-2">
          <label class="form-label">{{ __('global.type') }}</label>
          <select name="type" id="editType" class="form-select pm-select" required>
              <option value="" disabled selected>{{ __('global.modal_choose') }}</option>
              @foreach($types as $t)
                  @if ($t == 'admin')
                      <option value="{{ $t }}">{{ __('global.modal_administrator') }}</option>
                  @elseif (in_array($t, ['cobrador', 'vendedor']))
                      <option value="{{ $t }}">{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                  @elseif ($t == 'vendedor_cobrador')
                      <option value="{{ $t }}">{{ __('global.modal_salesman_collector') }}</option>
                  @else
                      <option value="{{ $t }}">{{ $t }}</option>
                  @endif
              @endforeach
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn pm-btn pm-btn-primary">{{ __('global.save') }}</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Reset Senha --}}
<div class="modal fade" id="modalReset" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content pm-form" method="POST" id="formReset">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">{{ __('global.reset_password') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">{{ __('global.new_password') }}</label>
          <input type="password" name="password" class="form-control pm-input" required minlength="4">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn pm-btn pm-btn-outline-danger">{{ __('global.redefine') }}</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
$('#modalCreate').on('show.bs.modal', function () {
    const select = $(this).find('select[name="type"]');
    select.val("");
});

(function(){
  let page = 1;
  const $q        = document.querySelector('input[name="q"]');
  const $tbody    = document.getElementById('users-tbody');
  const $btnPrev  = document.getElementById('btnPrev');
  const $btnNext  = document.getElementById('btnNext');
  const $range    = document.getElementById('users-range');
  const urlFetch  = @json(route('admin.users.fetch'));
  const confirmDeleteMsg = @json(__('global.delete_this_user'));

  function initTooltips(){
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
      .forEach(el => new bootstrap.Tooltip(el));
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

  // paginação
  $btnPrev?.addEventListener('click', function(){ if (page > 1){ page--; load(); } });
  $btnNext?.addEventListener('click', function(){ page++; load(); });

  // busca dinâmica
  let t;
  $q?.addEventListener('input', function(){
    page = 1;
    clearTimeout(t);
    t = setTimeout(load, 300);
  });

  // abrir modais (sem lógica de senha no Edit)
  const $modalEdit = document.getElementById('modalEdit');
  const $formEdit  = document.getElementById('formEdit');
  const $editType  = document.getElementById('editType');

  document.addEventListener('click', function(e){
    const btnEdit  = e.target.closest('.btn-edit');
    const btnReset = e.target.closest('.btn-reset');

    if (btnEdit) {
      const id   = btnEdit.getAttribute('data-id');
      const name = btnEdit.getAttribute('data-name');
      const type = btnEdit.getAttribute('data-type');

      document.getElementById('editName').value = name;
      if ($editType) $editType.value = type;
      if ($formEdit) $formEdit.setAttribute('action', '/admin/users/' + id);

      new bootstrap.Modal($modalEdit).show();
      return;
    }

    if (btnReset) {
      const id = btnReset.getAttribute('data-id');
      document.getElementById('formReset').setAttribute('action', '/admin/users/' + id + '/reset-password');
      new bootstrap.Modal(document.getElementById('modalReset')).show();
      return;
    }
  });

  document.addEventListener('DOMContentLoaded', load);
})();
</script>
@endpush
