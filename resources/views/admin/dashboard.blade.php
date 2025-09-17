@extends('layouts.admin')
@section('title','Dashboard')
@section('content')
<div class="row g-3">
  <div class="col-md-6">
    <div class="card shadow-sm pm-card">
      <div class="card-body">
        <h5 class="card-title pm-card-title">Produtos</h5>
        <p class="text-muted">Gerencie catálogo e preços.</p>
        <a href="{{ route('admin.products.index') }}" class="btn pm-btn pm-btn-primary">Abrir</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm pm-card">
      <div class="card-body">
        <h5 class="card-title pm-card-title">Usuários</h5>
        <p class="text-muted">Criar, alterar tipo, resetar senha.</p>
        <a href="{{ route('admin.users.index') }}" class="btn pm-btn pm-btn-primary">Abrir</a>
      </div>
    </div>
  </div>
</div>
@endsection
