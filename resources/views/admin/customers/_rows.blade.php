{{-- resources/views/admin/customers/_rows.blade.php --}}
@php
  use Illuminate\Support\Facades\Storage;
  $MUGSHOT = asset('storage/customers/mugshot/mugshot.png');
@endphp

@forelse ($items as $c)
  @php
    // Arquivos existem fisicamente?
    $avatarExists = $c->avatar_path && Storage::disk('public')->exists($c->avatar_path);
    $placeExists  = $c->place_path  && Storage::disk('public')->exists($c->place_path);

    // URLs só se existir o arquivo
    $avatarUrl = $avatarExists ? asset('storage/'.$c->avatar_path) : '';
    $placeUrl  = $placeExists  ? asset('storage/'.$c->place_path)  : '';

    // Coords: string vazia quando não numéricas
    $lat = is_numeric($c->lat) ? (string)$c->lat : '';
    $lng = is_numeric($c->lng) ? (string)$c->lng : '';

    // Miniatura na lista: usa avatar se existir, senão mugshot
    $thumbSrc = $avatarExists ? $avatarUrl : $MUGSHOT;
  @endphp

  <tr>
    <td style="width:120px">
      <img src="{{ $thumbSrc }}"
           alt="avatar"
           class="img-thumbnail"
           style="width:120px;height:120px;object-fit:cover;"
           onerror="this.onerror=null;this.src='{{ $MUGSHOT }}';">
    </td>

    <td>{{ $c->name }}</td>
    <td>{{ $c->city }}</td>
    <td>{{ $c->district }}</td>
    <td>{{ $c->cpf }}</td>
    <td>{{ $c->phone }}</td>

    <td class="text-end">
      <button type="button"
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
              data-lat="{{ is_numeric($c->lat) ? $c->lat : '' }}"
              data-lng="{{ is_numeric($c->lng) ? $c->lng : '' }}"
              data-avatar="{{ $avatarUrl }}"

              {{-- opcional: enviar também o place apenas se existir --}}
              data-place="{{ $placeUrl }}"

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
