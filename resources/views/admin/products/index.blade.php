@extends('layouts.admin')
@section('title','Produtos')

@section('content')
<div class="d-flex mb-3">
  <form class="d-flex" method="GET">
    <input type="text" name="q" value="{{ $q }}" class="form-control me-2" placeholder="Buscar por nome, modelo, cor">
    <button class="btn btn-outline-secondary">Buscar</button>
  </form>
  <a href="{{ route('admin.products.create') }}" class="btn btn-primary ms-auto">Novo</a>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Nome</th><th>Modelo</th><th>Cor</th><th>Tamanho</th><th>Preço</th><th class="text-end">Ações</th>
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
            <a href="{{ route('admin.products.edit',$p) }}" class="btn btn-sm btn-outline-primary">Editar</a>
            <form method="POST" action="{{ route('admin.products.destroy',$p) }}" class="d-inline delete-form">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">Excluir</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-muted">Nenhum produto.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    {{ $items->links() }}
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function(){
  $('.delete-form').on('submit', function(e){
    if(!confirm('Excluir este produto?')) e.preventDefault();
  });
});
</script>
@endpush
