@extends('layouts.admin')
@section('title','Produtos')

@section('content')
<div class="d-flex mb-3">
  <form class="d-flex pm-form" method="GET">
    <input type="text" name="q" value="{{ $q }}" class="form-control pm-input me-2" placeholder="Buscar por nome, modelo, cor">
    <button class="btn pm-btn pm-btn-outline-secondary">Buscar</button>
  </form>
  <a href="{{ route('admin.products.create') }}" class="btn pm-btn pm-btn-primary ms-auto">Novo</a>
</div>

<div class="card shadow-sm pm-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0 pm-table">
      <thead class="table-light pm-table-header">
        <tr>
          <th>Nome</th>
          <th>Modelo</th>
          <th>Cor</th>
          <th>Tamanho</th>
          <th>Preço</th>
          <th class="text-end">Ações</th>
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
                data-bs-toggle="tooltip" title="Editar">
                <i class="bi bi-pencil"></i>
            </a>

            <form method="POST" action="{{ route('admin.products.destroy',$p) }}" class="d-inline delete-form">
                @csrf @method('DELETE')
                <button class="btn btn-sm pm-btn pm-btn-outline-danger"
                        data-bs-toggle="tooltip" title="Excluir">
                <i class="bi bi-trash"></i>
                </button>
            </form>
            </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-muted pm-text-muted">Nenhum produto.</td>
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
        if(!confirm('Excluir este produto?')) e.preventDefault();
      });
    });
  });
</script>
@endpush
