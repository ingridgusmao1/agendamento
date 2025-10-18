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

{{-- Modal de Visualização de Imagem (avatar/local) --}}
<div class="modal fade" id="modalImagePreview" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Visualização</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('global.close') }}"></button>
      </div>
      <div class="modal-body text-center">
        <img id="imagePreviewTarget" src="" style="max-width:100%;max-height:70vh;object-fit:contain;">
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
(() => {
  // ------------------------------
  // Elements & state
  // ------------------------------
  const $q       = document.querySelector('input[name="q"]');
  const $tbody   = document.getElementById('tbodyRows');
  const $per     = document.getElementById('perPage');
  const $btnPrev = document.getElementById('btnPrevP');
  const $btnNext = document.getElementById('btnNextP');
  const $range   = document.getElementById('range');

  let page = 1;

  // ------------------------------
  // Utils
  // ------------------------------
  const show = (el) => { if (el) { el.style.display = ''; el.classList.remove('d-none'); } };
  const hide = (el) => { if (el) { el.style.display = 'none'; el.classList.add('d-none'); } };

  // normaliza valores vindos de data-* ('' | 'null' | 'undefined' => '')
  const norm = (v) => {
    if (v === undefined || v === null) return '';
    const s = String(v).trim();
    if (s === '' || s.toLowerCase() === 'null' || s.toLowerCase() === 'undefined') return '';
    return s;
  };

  const isNum = (v) => {
    const s = norm(v);
    if (s === '') return false;
    const n = Number(s);
    return Number.isFinite(n);
  };

  // testa a existência REAL do arquivo (mesmo domínio)
  async function urlExists(url) {
    try {
      if (!url) return false;
      const resp = await fetch(url, { method: 'HEAD', cache: 'no-store' });
      if (resp.ok) return true;
      // alguns servidores não permitem HEAD — tenta GET leve
      const respGet = await fetch(url, { method: 'GET', cache: 'no-store' });
      return respGet.ok;
    } catch {
      return false;
    }
  }

  // abre modal de zoom de imagem (se você já tiver um; ajusta conforme seu projeto)
  function openImageModal(src, title = '') {
    const m = document.getElementById('modalImagePreview');
    if (!m) return;
    const img = m.querySelector('img');
    const cap = m.querySelector('[data-title]');
    if (img) img.src = src || '';
    if (cap) cap.textContent = title || '';
    new bootstrap.Modal(m).show();
  }

  // ------------------------------
  // Render helpers (pager)
  // ------------------------------
  function updatePager(meta) {
    const hasPrev = !!meta?.has_prev;
    const hasNext = !!meta?.has_next;
    if ($btnPrev) $btnPrev.disabled = !hasPrev;
    if ($btnNext) $btnNext.disabled = !hasNext;
  }

  function updateRange(meta) {
    if (!$range || !meta) return;
    const total   = Number(meta.total ?? 0);
    const perPage = Number(meta.per_page ?? ($per ? $per.value : 15));
    const cur     = Number(meta.page ?? page);
    const from    = total === 0 ? 0 : ((cur - 1) * perPage) + 1;
    const to      = Math.min(cur * perPage, total);
    $range.textContent = `${from}–${to} / ${total}`;
  }

  // ------------------------------
  // Tabela: fetch + rebind
  // ------------------------------
  async function load() {
    const params = new URLSearchParams();
    if ($q && $q.value) params.set('q', $q.value);
    params.set('page', page);
    params.set('per_page', $per ? $per.value : 15);

    const url  = "{{ route('admin.customers.fetch') }}?" + params.toString();
    const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' });
    const meta = await resp.json();

    if ($tbody) $tbody.innerHTML = meta.html || '';

    updatePager(meta);
    updateRange(meta);
    initEditButtons();     // re-atacha eventos da tabela
    if (window.initTooltips) window.initTooltips(); // opcional
  }

  // ------------------------------
  // Modal de Edição: regras de mídia
  // ------------------------------
  function resetMediaBlocks($form) {
    const $blkUpload  = $form.querySelector('[data-block="upload-avatar"]');
    const $blkAvatar  = $form.querySelector('[data-block="avatar-preview"]');
    const $blkPlace   = $form.querySelector('[data-block="place-preview"]');
    const $blkMaps    = $form.querySelector('[data-block="maps-link"]');
    const $blkCoords  = $form.querySelectorAll('[data-block="coords"]'); // <— aqui!
    const $maps       = $form.querySelector('[data-maps-link]');
    const $avatarPrev = $form.querySelector('[data-avatar-preview]');
    const $placePrev  = $form.querySelector('[data-place-preview]');

    // esconde tudo
    hide($blkUpload); 
    hide($blkAvatar); 
    hide($blkPlace); 
    hide($blkMaps);
    // NodeList seguro (alguns browsers antigos): 
    ($blkCoords.forEach ? $blkCoords : Array.from($blkCoords)).forEach(hide);

    // limpa estados
    if ($avatarPrev){ $avatarPrev.src=''; $avatarPrev.onclick=null; }
    if ($placePrev){  $placePrev.src='';  $placePrev.onclick=null; }
    if ($maps){ $maps.href='#'; $maps.removeAttribute('target'); }
  }

  async function applyMediaRules($form, btn) {
    resetMediaBlocks($form);

    const $blkUpload = $form.querySelector('[data-block="upload-avatar"]');
    const $blkAvatar = $form.querySelector('[data-block="avatar-preview"]');
    const $blkPlace  = $form.querySelector('[data-block="place-preview"]');
    const $blkMaps   = $form.querySelector('[data-block="maps-link"]');
    const $blkCoords = $form.querySelectorAll('[data-block="coords"]');
    const $maps      = $form.querySelector('[data-maps-link]');
    const $avatarPrev= $form.querySelector('[data-avatar-preview]');
    const $placePrev = $form.querySelector('[data-place-preview]');

    // Estado vindo do botão (preenchido no _rows.blade.php)
    const avatar = norm(btn.dataset.avatar);
    const place  = norm(btn.dataset.place);
    const lat    = norm(btn.dataset.lat);
    const lng    = norm(btn.dataset.lng);

    // validações fortes
    const okAvatar = await urlExists(avatar);
    const okPlace  = await urlExists(place);
    const okGeo    = isNum(lat) && isNum(lng);

    const okAll = okAvatar && okPlace && okGeo;

    // Regra pedida: se QUALQUER falhar, não renderiza NADA relacionado à mídia
    if (!okAll) return;

    // Se passou em tudo, popula e mostra
    if ($avatarPrev){
      $avatarPrev.src = avatar;
      $avatarPrev.onclick = () => openImageModal(avatar, 'Foto do cliente');
    }
    if ($placePrev){
      $placePrev.src = place;
      $placePrev.onclick = () => openImageModal(place, 'Foto do local');
    }
    if ($maps){
      $maps.href = `https://www.google.com/maps?q=${lat},${lng}`;
      $maps.setAttribute('target','_blank');
    }

    show($blkAvatar);
    show($blkPlace);
    show($blkMaps);
    show($blkUpload);
    $blkCoords.forEach(show); // o pedido inclui esconder a "edição de foto" quando faltar algo; aqui só mostramos se tudo ok
  }

  function fillEditForm($form, btn) {
    const set = (name, val) => {
      const el = $form.querySelector(`[name="${name}"]`);
      if (el) el.value = (val ?? '');
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
    set('lat',             norm(btn.dataset.lat));
    set('lng',             norm(btn.dataset.lng));
  }

  function initEditButtons() {
    document.querySelectorAll('.btn-edit-customer').forEach(btn => {
      btn.addEventListener('click', async () => {
        const $form = document.querySelector('#modalEdit #formEdit');
        if (!$form) return;

        // ação do form
        const action = "{{ route('admin.customers.update', ':id') }}".replace(':id', btn.dataset.id);
        $form.setAttribute('action', action);

        // campos do formulário
        fillEditForm($form, btn);

        // mídia (aplica regra "tudo ou nada")
        await applyMediaRules($form, btn);

        new bootstrap.Modal(document.getElementById('modalEdit')).show();
      });
    });
  }

  // ------------------------------
  // Listeners
  // ------------------------------
  // busca com debounce
  let tId = null;
  if ($q) {
    $q.addEventListener('input', () => {
      clearTimeout(tId);
      tId = setTimeout(() => { page = 1; load(); }, 300);
    });
  }

  if ($per)     $per.addEventListener('change', () => { page = 1; load(); });
  if ($btnPrev) $btnPrev.addEventListener('click', () => { if (page > 1){ page--; load(); } });
  if ($btnNext) $btnNext.addEventListener('click', () => { page++; load(); });

  // ------------------------------
  // Boot
  // ------------------------------
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', load);
  } else {
    load();
  }
})();
</script>
@endpush
