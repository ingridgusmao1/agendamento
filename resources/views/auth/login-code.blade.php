{{-- resources/views/auth/login-code.blade.php --}}
@extends('layouts.guest')

@section('title', __('global.login'))

@section('content')
<div class="row justify-content-center mt-5">
  <div class="col-md-6 text-center">
    {{-- coloque o arquivo em public/logo/logo.jpg --}}
    <img src="{{ asset('logo/logo.jpg') }}" class="img-fluid mb-4" style="width:70%;" alt="Logo">
  </div>
</div>
<div class="row justify-content-center mt-5">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-header">{{ __('global.admin_access') }}</div>
      <div class="card-body">
        @if($errors->any())
          <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">{{ __('global.code') }}</label>
            <input type="text" name="code" class="form-control" value="{{ old('code') }}" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label">{{ __('global.password') }}</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100">{{ __('global.enter') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
