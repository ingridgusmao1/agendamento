@extends('layouts.admin')
@section('title', __('global.dashboard'))
@section('content')
<div class="row g-3">
  <div class="col-md-6">
    <div class="card shadow-sm pm-card">
      <div class="card-body">
        <h5 class="card-title pm-card-title position-relative">
          {{ __('global.products') }}
          <span id="lowStockBadgeProducts" class="badge-alert position-absolute end-0 translate-middle-y" style="top:-.35rem; display:none;">
            <i class="bi bi-exclamation-square-fill"></i>
            {{ __('global.low_stock') }}
          </span>
        </h5>
        <p class="text-muted">{{ __('global.products_caption') }}</p>
        <a href="{{ route('admin.products.index') }}" class="btn pm-btn pm-btn-primary">{{ __('global.open') }}</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm pm-card">
      <div class="card-body">
        <h5 class="card-title pm-card-title">{{ __('global.employees') }}</h5>
        <p class="text-muted">{{ __('global.users_caption') }}</p>
        <a href="{{ route('admin.users.index') }}" class="btn pm-btn pm-btn-primary">{{ __('global.open') }}</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm pm-card">
      <div class="card-body">
        <h5 class="card-title pm-card-title">{{ __('global.customers') }}</h5>
        <p class="text-muted">{{ __('global.customers_caption') }}</p>
        <a href="{{ route('admin.customers.index') }}" class="btn pm-btn pm-btn-primary">{{ __('global.open') }}</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm pm-card">
      <div class="card-body">
        <h5 class="card-title pm-card-title">{{ __('global.sales') }}</h5>
        <p class="text-muted">{{ __('global.sales_caption') }}</p>
        <a href="{{ route('admin.sales.index') }}" class="btn pm-btn pm-btn-primary">{{ __('global.open') }}</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm pm-card">
      <div class="card-body">
        <h5 class="card-title pm-card-title">{{ __('global.financial_reports') }}</h5>
        <p class="text-muted">{{ __('global.financial_reports_caption') }}</p>
        <a href="{{ route('admin.financial-reports.index') }}" class="btn pm-btn pm-btn-primary">{{ __('global.open') }}</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm pm-card">
      <div class="card-body">
        <h5 class="card-title pm-card-title">{{ __('global.installment_schedule') }}</h5>
        <p class="text-muted">{{ __('global.installment_schedule_caption') }}</p>
        <a href="{{ route('admin.installments-schedule.index') }}" class="btn pm-btn pm-btn-primary">{{ __('global.open') }}</a>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function() {
    // roda somente na dashboard (este arquivo)
    function checkLowStock() {
      $.ajax({
        url: '{{ route('admin.products.hasLowStock') }}',
        method: 'GET',
        cache: false,
        success: function (res) {
          if (res && res.low_stock === true) {
            $('#lowStockBadgeProducts').fadeIn(120);
          } else {
            $('#lowStockBadgeProducts').fadeOut(120);
          }
        }
      });
    }

    // dispara na carga
    $(document).ready(function () {
      checkLowStock();
      // Revalida a cada minuto
      setInterval(checkLowStock, 60000);
    });
  })();
</script>
@endpush