@forelse ($items as $p)
<tr>
  <td>{{ $p->name }}</td>
  <td>{{ $p->model }}</td>
  <td>{{ $p->color }}</td>
  <td>{{ $p->size }}</td>
  <td class="text-end">{{ number_format((float)$p->price, 2, ',', '.') }}</td>
  <td class="text-end">
    <button
      class="btn btn-sm pm-btn pm-btn-dark btn-edit-product"
      data-id="{{ $p->id }}"
      data-name="{{ e($p->name) }}"
      data-model="{{ e($p->model) }}"
      data-color="{{ e($p->color) }}"
      data-size="{{ e($p->size) }}"
      data-price="{{ $p->price }}"
      data-notes="{{ e($p->notes) }}"
      data-photo="{{ e($p->photo_path) }}"
      data-complements="{{ e($p->complements_text) }}"
      data-bs-toggle="tooltip" title="{{ __('global.edit') }}">
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
  <td colspan="6" class="text-muted text-center">{{ __('global.no_results') }}</td>
</tr>
@endforelse
