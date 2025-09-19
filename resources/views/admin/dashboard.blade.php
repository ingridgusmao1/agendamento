@extends('layouts.admin')
@section('title', __('global.dashboard'))
@section('content')
<div class="row g-3">
  <div class="col-md-6">
    <div class="card shadow-sm pm-card">
      <div class="card-body">
        <h5 class="card-title pm-card-title">{{ __('global.products') }}</h5>
        <p class="text-muted">{{ __('global.products_caption') }}</p>
        <a href="{{ route('admin.products.index') }}" class="btn pm-btn pm-btn-primary">{{ __('global.open') }}</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm pm-card">
      <div class="card-body">
        <h5 class="card-title pm-card-title">{{ __('global.users') }}</h5>
        <p class="text-muted">{{ __('global.users_caption') }}</p>
        <a href="{{ route('admin.users.index') }}" class="btn pm-btn pm-btn-primary">{{ __('global.open') }}</a>
      </div>
    </div>
  </div>
</div>
@endsection
