@php use Illuminate\Support\Str; @endphp

@forelse ($items as $p)
<tr>
  <td style="width:160px">
    @php
      $photos = $p->photo_path;
      if (!is_array($photos)) {
          if ($photos === null || $photos === '') {
              $photos = [];
          } else {
              $tmp = is_string($photos) ? json_decode($photos, true) : null;
              $photos = (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) ? $tmp : (is_string($photos) ? [$photos] : []);
          }
      }
    @endphp

    @if(count($photos))
      @php
        $rel = ltrim((string)$photos[0], '/'); // "products/ARQ.jpg"
        $url = Str::startsWith($rel, ['http://','https://','storage/','/storage/'])
          ? $rel
          : asset('storage/'.$rel);           // => /storage/products/ARQ.jpg
      @endphp
      <a href="{{ route('admin.products.gallery', $p) }}" title="Abrir galeria">
        <img src="{{ $url }}" alt="foto"
             class="img-thumbnail pm-thumb"
             style="width:160px;height:160px;object-fit:cover;">
      </a>
    @else
      <a href="{{ route('admin.products.gallery', $p) }}"
         class="d-inline-block text-center text-decoration-none"
         style="width:160px;height:160px;line-height:160px;border:1px dashed #ccc;">
        <span class="text-muted small">+ {{ __('global.add_images') ?? 'adicionar' }}</span>
      </a>
    @endif
  </td>

  <td>{{ $p->name }}</td>
  <td>{{ $p->model }}</td>
  <td>{{ $p->color }}</td>
  <td>{{ $p->size }}</td>
  <td class="text-end">{{ number_format((float)$p->price, 2, ',', '.') }}</td>

  {{-- Ações --}}
  <td class="text-end">
    <button
      type="button"
      class="btn btn-sm pm-btn pm-btn-dark btn-edit-product"
      data-id="{{ $p->id }}"
      data-name="{{ e((string)$p->name) }}"
      data-model="{{ e((string)$p->model) }}"
      data-color="{{ e((string)$p->color) }}"
      data-size="{{ e((string)$p->size) }}"
      data-price="{{ $p->price }}"
      data-notes="{{ e((string)$p->notes) }}"
      data-complements="{{ e($p->complements_text) }}"
      data-update-url="{{ route('admin.products.update', $p) }}"
      data-bs-toggle="modal"
      data-bs-target="#modalEdit"
      title="{{ __('global.edit') }}">
      <i class="bi bi-pencil"></i>
    </button>

    <form method="POST" action="{{ route('admin.products.destroy', $p) }}" class="d-inline delete-form">
      @csrf @method('DELETE')
      <button class="btn btn-sm pm-btn pm-btn-primary" data-bs-toggle="tooltip" title="{{ __('global.delete') }}">
        <i class="bi bi-trash"></i>
      </button>
    </form>
  </td>
</tr>
@empty
<tr>
  <td colspan="7" class="text-muted text-center">{{ __('global.no_results') }}</td>
</tr>
@endforelse
