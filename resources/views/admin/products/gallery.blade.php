@extends('layouts.admin')
@section('title', __('global.gallery'))

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">{{ __('global.gallery') }} — {{ $product->name }}</h5>
    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-secondary">{{ __('global.back') ?? 'Voltar' }}</a>
  </div>

  <div class="alert alert-info py-2">
    {{ __('global.max_images_notice') ?? 'Você pode anexar até 10 imagens por produto.' }}
  </div>

  <div class="d-flex gap-2 mb-3">
    {{-- Botão ADICIONAR (input multiple) --}}
    <form id="formUpload" action="{{ route('admin.products.images.upload', $product) }}" method="POST" enctype="multipart/form-data">
      @csrf
      <input id="photosInput" type="file" name="photos[]" accept="image/*" multiple class="d-none">
      <button id="btnAdd" type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="">
        {{ __('global.add_images') ?? 'Adicionar imagens' }}
      </button>
    </form>

    {{-- Botão REMOVER (abre modal) --}}
    <button id="btnOpenRemove" type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#removeModal">
      {{ __('global.remove_images') ?? 'Remover' }}
    </button>
  </div>

  {{-- nanoGALLERY2 container (grade principal) --}}
  <div id="product-gallery"></div>

  {{-- Modal de REMOÇÃO EM LOTE --}}
  <div class="modal fade" id="removeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title">{{ __('global.remove_images') ?? 'Remover imagens' }}</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('global.close') ?? 'Fechar' }}"></button>
        </div>

        <div class="modal-body">
          <div class="text-muted small mb-2">
            {{ __('global.select_images_to_remove') ?? 'Selecione uma ou mais imagens para remover.' }}
          </div>

          <form id="formDelete" action="{{ route('admin.products.images.batchDelete', $product) }}" method="POST">
            @csrf
            @method('DELETE')

            <div id="removeGrid" class="row g-3"></div>

            <div class="d-flex justify-content-between align-items-center mt-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="checkAll">
                <label class="form-check-label" for="checkAll">{{ __('global.select_all') ?? 'Selecionar tudo' }}</label>
              </div>

              <button id="btnConfirmDelete" type="submit" class="btn btn-danger" disabled>
                {{ __('global.remove_selected') ?? 'Remover selecionadas' }}
              </button>
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('global.cancel') ?? 'Cancelar' }}</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
(function() {
  const fetchUrl = @json(route('admin.products.images', $product));

  // i18n para JS
  const I18N = {
    MAX_REACHED:        @json(trans('global.max_images_reached')),
    CLICK_TO_ADD:       @json(trans('global.click_to_add_images')),
    MAX_EXPLAIN:        @json(trans('global.max_images_explain')),
    YOU_CAN_ADD_UP_TO:  @json(trans('global.you_can_add_up_to')),
    NO_IMAGES:          @json(trans('global.no_images')),
    CONFIRM_DELETE:     @json(trans('global.confirm_delete_selected')),
  };

  let maxImages = 10;              // será atualizado pelo backend na primeira carga
  let currentItems = [];           // [{ID, src, srct, title, kind}]

  // Cache de elementos
  const $btnAdd            = $('#btnAdd');
  const $photosInput       = $('#photosInput');
  const $formUpload        = $('#formUpload');
  const $removeGrid        = $('#removeGrid');
  const $btnConfirmDelete  = $('#btnConfirmDelete');
  const $checkAll          = $('#checkAll');
  const $gal               = $('#product-gallery');

  // Tooltip Bootstrap
  const tooltip = new bootstrap.Tooltip(document.getElementById('btnAdd'));

  // ---------- CARGA / RENDERIZAÇÃO DA GALERIA ----------
  function refreshGallery() {
    $.getJSON(fetchUrl, function(resp) {
      maxImages = Number(resp.maxImages || 10);

      const items = (resp.items || []).map(it => ({
        ID: String(it.ID),
        src: it.src,
        srct: it.srct || it.src,
        title: it.title || '',
        kind: it.kind || 'image'
      }));

      currentItems = items;

      // Reinicializa nanoGallery2 com o layout MOSAIC (Demo_Mosaic_1 adaptado)
      try { $gal.nanogallery2('destroy'); } catch(e) {}
      $gal.nanogallery2({
        items: items,

        // LAYOUT MOSAIC
        galleryMosaic : [
          { w: 2, h: 2, c: 1, r: 1 },
          { w: 1, h: 1, c: 3, r: 1 },
          { w: 1, h: 1, c: 3, r: 2 },
          { w: 1, h: 2, c: 4, r: 1 },
          { w: 2, h: 1, c: 5, r: 1 },
          { w: 2, h: 2, c: 5, r: 2 },
          { w: 1, h: 1, c: 4, r: 3 },
          { w: 2, h: 1, c: 2, r: 3 },
          { w: 1, h: 2, c: 1, r: 3 },
          { w: 1, h: 1, c: 2, r: 4 },
          { w: 2, h: 1, c: 3, r: 4 },
          { w: 1, h: 1, c: 5, r: 4 },
          { w: 1, h: 1, c: 6, r: 4 }
        ],
        galleryMosaicXS : [
          { w: 2, h: 2, c: 1, r: 1 },
          { w: 1, h: 1, c: 3, r: 1 },
          { w: 1, h: 1, c: 3, r: 2 },
          { w: 1, h: 2, c: 1, r: 3 },
          { w: 2, h: 1, c: 2, r: 3 },
          { w: 1, h: 1, c: 2, r: 4 },
          { w: 1, h: 1, c: 3, r: 4 }
        ],
        galleryMosaicSM : [
          { w: 2, h: 2, c: 1, r: 1 },
          { w: 1, h: 1, c: 3, r: 1 },
          { w: 1, h: 1, c: 3, r: 2 },
          { w: 1, h: 2, c: 1, r: 3 },
          { w: 2, h: 1, c: 2, r: 3 },
          { w: 1, h: 1, c: 2, r: 4 },
          { w: 1, h: 1, c: 3, r: 4 }
        ],
        galleryMaxRows: 1,
        galleryDisplayMode: 'rows',
        gallerySorting: 'random',
        thumbnailDisplayOrder: 'random',

        // Aparência dos thumbs (como no demo)
        thumbnailHeight: 180,
        thumbnailWidth: 220,
        thumbnailAlignment: 'scaled',
        thumbnailGutterWidth: 0,
        thumbnailGutterHeight: 0,
        thumbnailBorderHorizontal: 0,
        thumbnailBorderVertical: 0,

        // Toolbars/labels
        thumbnailToolbarImage: null,
        thumbnailToolbarAlbum: null,
        thumbnailLabel: { display: false },

        // Animações (demo)
        galleryDisplayTransitionDuration: 1500,
        thumbnailDisplayTransition: 'imageSlideUp',
        thumbnailDisplayTransitionDuration: 1200,
        thumbnailDisplayTransitionEasing: 'easeInOutQuint',
        thumbnailDisplayInterval: 60,

        // Hover/touch
        thumbnailBuildInit2: 'image_scale_1.15',
        thumbnailHoverEffect2: 'thumbnail_scale_1.00_1.05_300|image_scale_1.15_1.00',
        touchAnimation: true,
        touchAutoOpenDelay: 500,

        // Lightbox
        viewerToolbar: { display: false },
        viewerTools: {
          topLeft: 'label',
          topRight: 'shareButton, rotateLeft, rotateRight, fullscreenButton, closeButton'
        },

        // Tema
        galleryTheme : {
          thumbnail: { background: '#111' }
        },

        // Deep-link
        locationHash: true
      });

      updateAddState(items.length, maxImages);
    });
  }

  function updateAddState(count, limit) {
    if (count >= limit) {
      $btnAdd.prop('disabled', true);
      $btnAdd.attr('data-bs-original-title', I18N.MAX_REACHED);
    } else {
      $btnAdd.prop('disabled', false);
      $btnAdd.attr('data-bs-original-title', I18N.CLICK_TO_ADD);
    }
    tooltip.update();
  }

  // ---------- UPLOAD ----------
  $btnAdd.on('click', () => $photosInput.trigger('click'));

  $photosInput.on('change', function() {
    const selected = this.files ? this.files.length : 0;
    if (!selected) return;

    const total = currentItems.length + selected;
    if (total > maxImages) {
      const left = Math.max(0, maxImages - currentItems.length);
      alert(I18N.MAX_EXPLAIN + ' ' + I18N.YOU_CAN_ADD_UP_TO + ' ' + left + '.');
      $photosInput.val('');
      return;
    }
    $formUpload.trigger('submit');
  });

  // ---------- MODAL DE REMOÇÃO ----------
  const removeModalEl = document.getElementById('removeModal');

  removeModalEl.addEventListener('show.bs.modal', () => {
    renderRemoveGrid(currentItems);
    updateDeleteButtonState();
  });

  removeModalEl.addEventListener('hidden.bs.modal', () => {
    $removeGrid.empty();
    $btnConfirmDelete.prop('disabled', true);
    $checkAll.prop('checked', false);
  });

  function renderRemoveGrid(items) {
    $removeGrid.empty();
    if (!items.length) {
      $removeGrid.append('<div class="col-12 text-muted">' + I18N.NO_IMAGES + '</div>');
      return;
    }

    items.forEach((it) => {
      const col = $(`
        <div class="col-6 col-md-3 col-lg-2">
          <label class="w-100 d-block border rounded p-1" style="cursor:pointer;">
            <div class="ratio ratio-1x1 mb-2" style="max-width:160px; max-height:160px; margin:0 auto;">
              <img src="${it.srct || it.src}" alt="" class="w-100 h-100" style="object-fit:cover;">
            </div>
            <div class="form-check text-center">
              <input class="form-check-input chk-item" type="checkbox" value="${it.ID}">
              <span class="small text-muted">#${it.ID}</span>
            </div>
          </label>
        </div>
      `);
      $removeGrid.append(col);
    });

    // Selecionar tudo
    $checkAll.off('change').on('change', function() {
      const checked = $(this).is(':checked');
      $removeGrid.find('.chk-item').prop('checked', checked);
      updateDeleteButtonState();
    });

    // Habilitar/Desabilitar botão remover
    $removeGrid.find('.chk-item').on('change', function() {
      updateDeleteButtonState();
      if (!$(this).is(':checked')) {
        $checkAll.prop('checked', false);
      }
    });

    // Submit com confirmação e indexes[]
    $('#formDelete').off('submit').on('submit', function(e) {
      e.preventDefault();
      const ids = getSelectedIds();
      if (!ids.length) return;

      if (!confirm(I18N.CONFIRM_DELETE)) {
        return;
      }

      const $form = $(this);
      $form.find('input[name="indexes[]"]').remove();
      ids.forEach(id => $form.append(`<input type="hidden" name="indexes[]" value="${id}">`));
      e.currentTarget.submit();
    });
  }

  function getSelectedIds() {
    const ids = [];
    $removeGrid.find('.chk-item:checked').each(function() {
      const val = $(this).val();
      if (val !== undefined && val !== null && val !== '') ids.push(val);
    });
    return ids;
  }

  function updateDeleteButtonState() {
    $btnConfirmDelete.prop('disabled', getSelectedIds().length === 0);
  }

  // Inicialização
  refreshGallery();
})();
</script>
@endpush
