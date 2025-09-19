@forelse ($items as $c)
<tr>
  {{-- Foto (única, não clicável) --}}
  <td style="width:120px">
    @php
      $src = $c->avatar_path
        ? asset($c->avatar_path)   // ex.: public/customers/...
        : 'https://via.placeholder.com/240?text=Avatar';
    @endphp
    <img src="{{ $src }}" alt="avatar" class="img-thumbnail"
         style="width:120px;height:120px;object-fit:cover;">
  </td>

  <td>{{ $c->name }}</td>
  <td>{{ $c->city }}</td>
  <td>{{ $c->district }}</td>
  <td>{{ $c->cpf }}</td>
  <td>{{ $c->phone }}</td>

  <td class="text-end">
    <button
      type="button"
      class="btn btn-sm pm-btn pm-btn-dark btn-edit-customer"
      data-id="{{ $c->id }}"
      data-name="{{ e((string)$c->name) }}"
      data-street="{{ e((string)$c->street) }}"
      data-number="{{ e((string)$c->number) }}"
      data-district="{{ e((string)$c->district) }}"
      data-city="{{ e((string)$c->city) }}"
      data-reference_point="{{ e((string)$c->reference_point) }}"
      data-rg="{{ e((string)$c->rg) }}"
      data-cpf="{{ e((string)$c->cpf) }}"
      data-phone="{{ e((string)$c->phone) }}"
      data-other_contact="{{ e((string)$c->other_contact) }}"
      data-lat="{{ $c->lat }}"
      data-lng="{{ $c->lng }}"
      data-avatar="{{ $c->avatar_path ? asset($c->avatar_path) : '' }}"
      data-bs-toggle="tooltip" title="{{ __('global.edit') }}">
      <i class="bi bi-pencil"></i>
    </button>

    <form method="POST" action="{{ route('admin.customers.destroy', $c) }}" class="d-inline delete-form">
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
