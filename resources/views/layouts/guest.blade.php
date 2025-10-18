<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>@yield('title','Login')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- se usa Vite --}}
  @vite(['resources/css/app.css','resources/js/app.js'])
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>body{background:#fff}</style>
  @stack('styles')
</head>
<body>
  <main class="container py-4">
    @yield('content')
  </main>
  @stack('scripts')
</body>
</html>
