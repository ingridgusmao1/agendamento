@extends('layouts.admin')
@section('title',__('global.products'))

@section('content')
<div class="d-flex mb-3">
  <form class="d-flex pm-form" method="GET">
    <input type="text" name="q" value="{{ $q }}" class="form-control pm-input me-2" placeholder="Buscar por nome, modelo, cor">
    <button class="btn pm-btn pm-btn-outline-secondary">Buscar</button>
  </form>
  <a href="{{ route('admin.products.create') }}" class="btn pm-btn pm-btn-primary ms-auto">{{ __('global.new') }}</a>
</div>

<div class="card shadow-sm pm-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0 pm-table">
      <thead class="table-light pm-table-header">
        <tr>
          <th>{{ __('global.name') }}</th>
          <th>{{ __('global.model') }}</th>
          <th>{{ __('global.color') }}</th>
          <th>{{ __('global.size') }}</th>
          <th>{{ __('global.price') }}</th>
          <th class="text-end">{{ __('global.actions') }}</th>
        </tr>
      </thead>
      <tbody>
      @forelse($items as $p)
        <tr>
            <td>{{ $p->name }}</td>
            <td>{{ $p->model }}</td>
            <td>{{ $p->color }}</td>
            <td>{{ $p->size }}</td>
            <td>R$ {{ number_format((float)$p->price,2,',','.') }}</td>
            <td class="text-end">
            <a href="{{ route('admin.products.edit',$p) }}"
                class="btn btn-sm pm-btn pm-btn-dark"
                data-bs-toggle="tooltip" title="{{ __('global.edit') }}">
                <i class="bi bi-pencil"></i>
            </a>

            <form method="POST" action="{{ route('admin.products.destroy',$p) }}" class="d-inline delete-form">
                @csrf @method('DELETE')
                <button class="btn btn-sm pm-btn pm-btn-outline-danger"
                        data-bs-toggle="tooltip" title="{{ __('global.delete') }}">
                <i class="bi bi-trash"></i>
                </button>
            </form>
            </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-muted pm-text-muted">{{ __('global.no_product') }}</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer pm-card-footer">
    {{ $items->links() }}
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.delete-form').forEach(f => {
      f.addEventListener('submit', (e) => {
        if(!confirm(@json(__('global.exclude_this_product')))) e.preventDefault();
      });
    });
  });
</script>
@endpush
