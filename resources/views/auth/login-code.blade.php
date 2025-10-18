{{-- resources/views/auth/login-code.blade.php --}}
@extends('layouts.guest')

@section('title', __('global.login'))

@section('content')
<div class="row justify-content-center mt-5">
  <div class="col-md-6 text-center">
    {{-- coloque o logo em public/logo/logo.jpg --}}
    <img src="{{ asset('logo/logo.jpg') }}" class="img-fluid mb-4" style="width:70%;" alt="Logo">
  </div>
</div>
<div class="row justify-content-center mt-5">
  <div class="col-md-4">
    <div class="card shadow-sm pm-card">
      <div class="card-header pm-card-header">{{ __('global.admin_access') }}</div>
      <div class="card-body pm-card-body">
        @if($errors->any())
          <div class="alert alert-danger pm-alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label pm-label">{{ __('global.code') }}</label>
            <input type="text" name="code" class="form-control pm-input" value="{{ old('code') }}" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label pm-label">{{ __('global.password') }}</label>
            <input type="password" name="password" class="form-control pm-input" required>
          </div>
          <button class="btn pm-btn pm-btn-primary w-100 d-flex justify-content-center">
            {{ __('global.enter') }}
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
