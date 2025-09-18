@extends('layouts.admin')
@section('title', __('global.gallery'))

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">{{ __('global.gallery') }} — {{ $product->name }}</h5>
    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-secondary">Voltar</a>
  </div>

  {{-- Container do nanogallery2 --}}
  <div id="nanogallery"
       data-fetch-url="{{ route('admin.products.images', $product) }}">
  </div>
@endsection

@push('scripts')
<script>
(function() {
  const $gal = $('#nanogallery');
  const fetchUrl = $gal.data('fetch-url');

  $.getJSON(fetchUrl, function(resp) {
    const items = (resp.items || []).map(function(it) {
      return { src: it.src, srct: it.srct, title: it.title, ID: it.ngid, kind: 'image' };
    });

    // Inicialização “modelo index.php”: apenas o JS da galeria aqui
    $gal.nanogallery2({
      items: items,

      // Layout e thumbs (pode ajustar depois)
      galleryMosaic : [
        { w:2,h:2,c:1,r:1 },{ w:1,h:1,c:3,r:1 },{ w:1,h:1,c:3,r:2 },
        { w:1,h:2,c:4,r:1 },{ w:2,h:1,c:5,r:1 },{ w:2,h:2,c:5,r:2 },
        { w:1,h:1,c:4,r:3 },{ w:2,h:1,c:2,r:3 },{ w:1,h:2,c:1,r:3 },
        { w:1,h:1,c:2,r:4 },{ w:2,h:1,c:3,r:4 },{ w:1,h:1,c:5,r:4 },{ w:1,h:1,c:6,r:4 }
      ],
      galleryMosaicXS : [
        { w:2,h:2,c:1,r:1 },{ w:1,h:1,c:3,r:1 },{ w:1,h:1,c:3,r:2 },
        { w:1,h:2,c:1,r:3 },{ w:2,h:1,c:2,r:3 },{ w:1,h:1,c:2,r:4 },{ w:1,h:1,c:3,r:4 }
      ],
      galleryMosaicSM : [
        { w:2,h:2,c:1,r:1 },{ w:1,h:1,c:3,r:1 },{ w:1,h:1,c:3,r:2 },
        { w:1,h:2,c:1,r:3 },{ w:2,h:1,c:2,r:3 },{ w:1,h:1,c:2,r:4 },{ w:1,h:1,c:3,r:4 }
      ],
      galleryMaxRows: 1,
      galleryDisplayMode: 'rows',
      gallerySorting: 'random',
      thumbnailDisplayOrder: 'random',
      thumbnailHeight: '180',
      thumbnailWidth: '220',
      thumbnailAlignment: 'scaled',
      thumbnailGutterWidth: 0,
      thumbnailGutterHeight: 0,
      thumbnailBorderHorizontal: 0,
      thumbnailBorderVertical: 0,
      thumbnailToolbarImage: null,
      thumbnailToolbarAlbum: null,
      thumbnailLabel: { display: false },

      // Animações
      galleryDisplayTransitionDuration: 1500,
      thumbnailDisplayTransition: 'imageSlideUp',
      thumbnailDisplayTransitionDuration: 1200,
      thumbnailDisplayTransitionEasing: 'easeInOutQuint',
      thumbnailDisplayInterval: 60,

      // Hover
      thumbnailBuildInit2: 'image_scale_1.15',
      thumbnailHoverEffect2: 'thumbnail_scale_1.00_1.05_300|image_scale_1.15_1.00',
      touchAnimation: true,
      touchAutoOpenDelay: 500,

      // Lightbox
      viewerToolbar: { display: false },
      viewerTools: { topLeft: 'label', topRight: 'shareButton, rotateLeft, rotateRight, fullscreenButton, closeButton' },

      // Tema
      galleryTheme : { thumbnail: { background: '#111' } },

      // Deep linking
      locationHash: true
    });
  });
})();
</script>
@endpush
