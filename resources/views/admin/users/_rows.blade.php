@forelse ($items as $u)
<tr>
  <td>{{ $u->code }}</td>
  <td>{{ $u->name }}</td>
  <td>{{ $u->type }}</td>
  <td class="text-end">
    <button
      class="btn btn-sm pm-btn pm-btn-dark btn-edit"
      data-id="{{ $u->id }}" data-name="{{ $u->name }}" data-type="{{ $u->type }}"
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
  <td colspan="4" class="text-muted text-center">{{ __('global.no_results') }}</td>
</tr>
@endforelse
