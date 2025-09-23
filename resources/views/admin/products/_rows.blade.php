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

  <td class="text-end">
    {{-- ... botões/ações como já estavam ... --}}
  </td>
</tr>
@empty
<tr>
  <td colspan="7" class="text-muted text-center">{{ __('global.no_results') }}</td>
</tr>
@endforelse
