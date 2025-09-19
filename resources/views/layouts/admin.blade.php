<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','Painel')</title>

  {{-- Bootstrap + ícones + seu CSS --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">

  {{-- nanogallery2 CSS --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nanogallery2/3.0.5/css/nanogallery2.min.css" referrerpolicy="no-referrer" />

  <style>
    body { background:#f7f7fb; }
    .navbar-brand { font-weight:600; }
  </style>

  @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Prestamista • Admin</a>
    <div class="d-flex">
      @auth
      <form method="POST" action="{{ route('logout') }}" class="ms-2">
        @csrf
        <button class="btn btn-outline-light btn-sm">Sair</button>
      </form>
      @endauth
    </div>
  </div>
</nav>

<div class="container py-4">
  @if(session('ok'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('ok') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
  @endif

  @if(session('err'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('err') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
  @endif

  @yield('content')
</div>

{{-- JS base --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- nanogallery2 JS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/nanogallery2/3.0.5/jquery.nanogallery2.min.js" referrerpolicy="no-referrer"></script>

@stack('scripts')
</body>
</html>
