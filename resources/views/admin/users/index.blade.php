@extends('layouts.admin')
@section('title','Usuários')

@section('content')
<div class="d-flex mb-3">
  <form class="d-flex" method="GET">
    <input type="text" name="q" value="{{ $q }}" class="form-control me-2" placeholder="Buscar por nome ou código">
    <button class="btn btn-outline-secondary">Buscar</button>
  </form>
  <button class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalCreate">Novo</button>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead class="table-light">
        <tr>
          <th>Código</th><th>Nome</th><th>Tipo</th><th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
      @forelse($items as $u)
        <tr>
          <td>{{ $u->code }}</td>
          <td>{{ $u->name }}</td>
          <td>{{ $u->type }}</td>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-primary btn-edit"
                data-id="{{ $u->id }}" data-name="{{ $u->name }}" data-type="{{ $u->type }}">Editar</button>
            <button class="btn btn-sm btn-outline-warning btn-reset"
                data-id="{{ $u->id }}">Resetar senha</button>
            <form method="POST" action="{{ route('admin.users.destroy',$u) }}" class="d-inline delete-form">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">Excluir</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-muted">Nenhum usuário.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    {{ $items->links() }}
  </div>
</div>

{{-- Modal Novo --}}
<div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="{{ route('admin.users.store') }}">
      @csrf
      <div class="modal-header"><h5 class="modal-title">Novo usuário</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Código</label>
          <input name="code" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Nome</label>
          <input name="name" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Tipo</label>
          <select name="type" class="form-select" required>
            @foreach($types as $t) <option value="{{ $t }}">{{ $t }}</option> @endforeach
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">Senha</label>
          <input type="password" name="password" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Salvar</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Editar --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" id="formEdit">
      @csrf @method('PUT')
      <div class="modal-header"><h5 class="modal-title">Editar usuário</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Nome</label>
          <input name="name" id="editName" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Tipo</label>
          <select name="type" id="editType" class="form-select" required>
            @foreach($types as $t) <option value="{{ $t }}">{{ $t }}</option> @endforeach
          </select>
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-primary">Salvar</button></div>
    </form>
  </div>
</div>

{{-- Modal Reset Senha --}}
<div class="modal fade" id="modalReset" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" id="formReset">
      @csrf
      <div class="modal-header"><h5 class="modal-title">Redefinir senha</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Nova senha</label>
          <input type="password" name="password" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-warning">Redefinir</button></div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function(){
  $('.delete-form').on('submit', function(e){
    if(!confirm('Excluir este usuário?')) e.preventDefault();
  });

  $('.btn-edit').on('click', function(){
    const id = $(this).data('id');
    $('#editName').val($(this).data('name'));
    $('#editType').val($(this).data('type'));
    $('#formEdit').attr('action', '/admin/users/'+id);
    new bootstrap.Modal('#modalEdit').show();
  });

  $('.btn-reset').on('click', function(){
    const id = $(this).data('id');
    $('#formReset').attr('action', '/admin/users/'+id+'/reset-password');
    new bootstrap.Modal('#modalReset').show();
  });
});
</script>
@endpush
