@php
  $CUSTOMER_AVATAR_PLACEHOLDER = 'data:image/svg+xml;utf8,' . rawurlencode(
    '<svg xmlns="http://www.w3.org/2000/svg" width="240" height="240">
       <rect width="100%" height="100%" fill="#eeeeee"/>
       <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
             fill="#888888" font-family="Arial, Helvetica, sans-serif" font-size="20">
         Avatar
       </text>
     </svg>'
  );
@endphp

@forelse ($items as $c)
<tr>
  <td style="width:120px">
    @php
      $src = $c->avatar_path ? asset('storage/'.$c->avatar_path) : $CUSTOMER_AVATAR_PLACEHOLDER;
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
    <button type="button" class="btn btn-sm pm-btn pm-btn-dark btn-edit-customer"
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
      {{-- corrige: caminho do storage para o avatar --}}
      data-avatar="{{ $c->avatar_path ? asset('storage/'.$c->avatar_path) : $CUSTOMER_AVATAR_PLACEHOLDER }}"
      {{-- novo: foto do local (somente visualização) --}}
      data-place="{{ $c->place_path ? asset('storage/'.$c->place_path) : '' }}"
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
