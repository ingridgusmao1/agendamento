@forelse ($items as $u)
<tr>
  {{-- code --}}
  <td>{{ $u->code }}</td>

  {{-- name --}}
  <td>{{ $u->name }}</td>

  {{-- type (com tradução existente) --}}
  <td>
    @if($u->type === 'admin')
      {{ __('global.modal_administrator') }}
    @elseif($u->type === 'vendedor')
      {{ __('global.salesman') }}
    @elseif($u->type === 'cobrador')
      {{ __('global.collector') }}
    @elseif($u->type === 'vendedor_cobrador')
      {{ __('global.modal_salesman_collector') }}
    @else
      {{ $u->type }}
    @endif
  </td>

  {{-- seller_mode (apenas exibição) --}}
  <td>
    @php $mode = $u->store_mode ?? ''; @endphp
    @switch($mode)
      @case('externo') Externo @break
      @case('loja')    Loja    @break
      @case('ambos')   Ambos   @break
      @case('outro')   Outro   @break
      @default         —
    @endswitch
  </td>

  {{-- actions --}}
  <td class="text-end">
    <button
      class="btn btn-sm pm-btn pm-btn-dark btn-edit"
      data-id="{{ $u->id }}"
      data-name="{{ $u->name }}"
      data-type="{{ $u->type }}"
      data-store-mode="{{ $u->store_mode ?? '' }}"
      data-bs-toggle="tooltip" title="{{ __('global.edit') }}">
      <i class="bi bi-pencil"></i>
    </button>

    <button
      class="btn btn-sm pm-btn pm-btn-outline-danger btn-reset"
      data-id="{{ $u->id }}"
      data-bs-toggle="tooltip" title="{{ __('global.reset_password') }}">
      <i class="bi bi-key"></i>
    </button>

    @if($u->type !== 'admin')
      <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="d-inline delete-form">
        @csrf @method('DELETE')
        <button class="btn btn-sm pm-btn pm-btn-primary" data-bs-toggle="tooltip" title="{{ __('global.delete') }}">
          <i class="bi bi-trash"></i>
        </button>
      </form>
    @endif
  </td>
</tr>
@empty
<tr>
  <td colspan="5" class="text-muted text-center">{{ __('global.no_results') }}</td>
</tr>
@endforelse
