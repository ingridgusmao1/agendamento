<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>@yield('title','Login')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Bootstrap (CDN) --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  {{-- (Opcional) seu CSS plano, caso exista em public/css/style.css --}}
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">

  {{-- CSRF para formulários --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  @stack('styles')
  <style>body{background:#fff}</style>
</head>
<body>
  <main class="container py-4">
    @yield('content')
  </main>

  {{-- Bootstrap JS (CDN) --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  @stack('scripts')
</body>
</html>
