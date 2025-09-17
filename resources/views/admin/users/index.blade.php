@extends('layouts.admin')
@section('title','Usuários')

@section('content')
<div class="d-flex mb-3">
  <form class="d-flex pm-form" method="GET">
    <input type="text" name="q" value="{{ $q }}" class="form-control pm-input me-2" placeholder="Buscar por nome ou código">
    <button class="btn pm-btn pm-btn-outline-secondary">Buscar</button>
  </form>
  <button class="btn pm-btn pm-btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalCreate">
    <i class="bi bi-plus-lg"></i> Novo
  </button>
</div>

<div class="card shadow-sm pm-card">
  <div class="table-responsive">
    <table class="table align-middle mb-0 pm-table">
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
            <button
              class="btn btn-sm pm-btn pm-btn-dark btn-edit"
              data-id="{{ $u->id }}" data-name="{{ $u->name }}" data-type="{{ $u->type }}"
              data-bs-toggle="tooltip" title="Editar">
              <i class="bi bi-pencil"></i>
            </button>

            <button
              class="btn btn-sm pm-btn pm-btn-outline-danger btn-reset"
              data-id="{{ $u->id }}"
              data-bs-toggle="tooltip" title="Resetar senha">
              <i class="bi bi-key"></i>
            </button>

            <form method="POST" action="{{ route('admin.users.destroy',$u) }}" class="d-inline delete-form">
              @csrf @method('DELETE')
              <button
                class="btn btn-sm pm-btn pm-btn-primary"
                data-bs-toggle="tooltip" title="Excluir">
                <i class="bi bi-trash"></i>
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="4" class="text-muted pm-text-muted">Nenhum usuário.</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer pm-card-footer">
    {{ $items->links() }}
  </div>
</div>

{{-- Modal Novo --}}
<div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content pm-form" method="POST" action="{{ route('admin.users.store') }}">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Novo usuário</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Código</label>
          <input name="code" class="form-control pm-input" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Nome</label>
          <input name="name" class="form-control pm-input" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Tipo</label>
          <select name="type" class="form-select pm-select" required>
            @foreach($types as $t) <option value="{{ $t }}">{{ $t }}</option> @endforeach
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">Senha</label>
          <input type="password" name="password" class="form-control pm-input" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn pm-btn pm-btn-primary">Salvar</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Editar --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content pm-form" method="POST" id="formEdit">
      @csrf @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title">Editar usuário</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Nome</label>
          <input name="name" id="editName" class="form-control pm-input" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Tipo</label>
          <select name="type" id="editType" class="form-select pm-select" required>
            @foreach($types as $t) <option value="{{ $t }}">{{ $t }}</option> @endforeach
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn pm-btn pm-btn-primary">Salvar</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Reset Senha --}}
<div class="modal fade" id="modalReset" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content pm-form" method="POST" id="formReset">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Redefinir senha</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Nova senha</label>
          <input type="password" name="password" class="form-control pm-input" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn pm-btn pm-btn-outline-danger">Redefinir</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Confirmação de exclusão
  document.querySelectorAll('.delete-form').forEach(f => {
    f.addEventListener('submit', (e) => {
      if(!confirm('Excluir este usuário?')) e.preventDefault();
    });
  });

  // Abrir modal de edição
  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const id   = btn.getAttribute('data-id');
      const name = btn.getAttribute('data-name');
      const type = btn.getAttribute('data-type');
      document.getElementById('editName').value = name;
      document.getElementById('editType').value = type;
      document.getElementById('formEdit').setAttribute('action', '/admin/users/'+id);
      new bootstrap.Modal(document.getElementById('modalEdit')).show();
    });
  });

  // Abrir modal de reset de senha
  document.querySelectorAll('.btn-reset').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      document.getElementById('formReset').setAttribute('action', '/admin/users/'+id+'/reset-password');
      new bootstrap.Modal(document.getElementById('modalReset')).show();
    });
  });

  // Tooltips (BS5)
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
</script>
@endpush
