<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','Painel')</title>

  {{-- Bootstrap + ícones + seu CSS --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  {{-- Bootstrap Icons CSS (mantido como estava) --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  {{-- PRELOAD da fonte dos ícones (adição mínima p/ garantir download da glyph font) --}}
  <link rel="preload"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff2"
        as="font" type="font/woff2" crossorigin>

  <link rel="stylesheet" href="{{ asset('css/style.css') }}">

  {{-- nanogallery2 CSS (mantido) --}}
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/nanogallery2/3.0.5/css/nanogallery2.min.css"
        referrerpolicy="no-referrer" />

  <style>
    /* QUALQUER CSS LOCAL ESPECÍFICO DO LAYOUT QUE VOCÊ JÁ TINHA, MANTENHA AQUI */
    .pm-container { max-width: 1400px; margin: 0 auto; } /* exemplo de centralização desktop se já usava */
  </style>

  @stack('styles')
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container-xxl">
    @unless (request()->routeIs('login'))
      <a class="navbar-brand d-flex align-items-center" href="{{ route('admin.dashboard') }}">
        <i class="bi bi-table me-2"></i> Cred Lar
      </a>
    @endunless

    <div class="d-flex align-items-center">
      @auth
      <form action="{{ route('logout') }}" method="POST" class="ms-3">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-light">
          <i class="bi bi-box-arrow-right me-1"></i>{{ __('global.logout') }}
        </button>
      </form>
      @endauth
    </div>
  </div>
</nav>

<div class="container-xxl py-3 pm-container">
  {{-- Alerts padrão, mantidos --}}
  @if (session('ok'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('ok') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
  @endif

  @if (session('err'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('err') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
  @endif

  @yield('content')
</div>

{{-- JS base (mantidos) --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- nanogallery2 JS (mantido) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/nanogallery2/3.0.5/jquery.nanogallery2.min.js"
        referrerpolicy="no-referrer"></script>

<script>window.initTooltips = window.initTooltips || function(){};</script>

@stack('scripts')
</body>
</html>

