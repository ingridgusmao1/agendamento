@php
    use Illuminate\Support\Facades\Storage;
@endphp

@forelse ($items as $p)
<tr>
  <td style="width:140px">
    <div class="d-flex flex-wrap gap-1">
      @php
        // Blindagem local (caso algum valor legado chegue como string)
        $photos = $p->photo_path;
        if (!is_array($photos)) {
            if ($photos === null || $photos === '') $photos = [];
            else {
                $tmp = json_decode($photos, true);
                $photos = (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) ? $tmp : [$photos];
            }
        }
      @endphp

      @forelse($photos as $path)
        @if($path)
          <img src="{{ Storage::url($path) }}" alt="foto" class="img-thumbnail"
               style="width:60px;height:60px;object-fit:cover;">
        @endif
      @empty
        <span class="text-muted small">—</span>
      @endforelse
    </div>
  </td>

  <td>{{ $p->name }}</td>
  <td>{{ $p->model }}</td>
  <td>{{ $p->color }}</td>
  <td>{{ $p->size }}</td>
  <td class="text-end">{{ number_format((float)$p->price, 2, ',', '.') }}</td>

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
  {{-- 7 porque é o número atual de colunas. --}}
  <td colspan="7" class="text-muted text-center">{{ __('global.no_results') }}</td>
</tr>
@endforelse
