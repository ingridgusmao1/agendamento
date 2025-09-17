@extends('layouts.admin')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center mt-5">
  <div class="col-md-4">
    <div class="card shadow-sm pm-card">
      <div class="card-header pm-card-header">Acesso do Administrador</div>
      <div class="card-body pm-card-body">
        @if($errors->any())
          <div class="alert alert-danger pm-alert">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login.post') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label pm-label">Código</label>
            <input type="text" name="code" class="form-control pm-input" value="{{ old('code') }}" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label pm-label">Senha</label>
            <input type="password" name="password" class="form-control pm-input" required>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input pm-check" type="checkbox" name="remember" id="remember">
            <label class="form-check-label pm-label" for="remember">Lembrar</label>
          </div>
          <button class="btn pm-btn pm-btn-primary w-100 d-flex justify-content-center">Entrar</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
