<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>@yield('title','Login')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Bootstrap (mesma base do admin) --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  {{-- Bootstrap Icons, se seu admin usa --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preload"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff2"
        as="font" type="font/woff2" crossorigin>

  {{-- Seus estilos para manter as classes pm-* iguais ao admin --}}
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">

  {{-- CSRF --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <style>body{background:#fff !important}</style>
  @stack('styles')
</head>
<body>
  <main class="container-xxl py-4">
    @yield('content')
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
