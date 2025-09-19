@php
  // usado tanto no create quanto no edit
@endphp
<div class="row g-3">
  <div class="col-md-8">
    <label class="form-label">{{ __('global.name') }}</label>
    <input type="text" name="name" class="form-control" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">{{ __('global.phone') }}</label>
    <input type="text" name="phone" class="form-control">
  </div>

  <div class="col-md-6">
    <label class="form-label">{{ __('global.city') }}</label>
    <input type="text" name="city" class="form-control">
  </div>
  <div class="col-md-6">
    <label class="form-label">{{ __('global.district') }}</label>
    <input type="text" name="district" class="form-control">
  </div>

  <div class="col-md-6">
    <label class="form-label">{{ __('global.street') }}</label>
    <input type="text" name="street" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('global.number') }}</label>
    <input type="text" name="number" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label">{{ __('global.reference_point') }}</label>
    <input type="text" name="reference_point" class="form-control">
  </div>

  <div class="col-md-4">
    <label class="form-label">{{ __('global.cpf') }}</label>
    <input type="text" name="cpf" class="form-control">
  </div>
  <div class="col-md-4">
    <label class="form-label">{{ __('global.rg') }}</label>
    <input type="text" name="rg" class="form-control">
  </div>
  <div class="col-md-2">
    <label class="form-label">Lat</label>
    <input type="number" step="any" name="lat" class="form-control">
  </div>
  <div class="col-md-2">
    <label class="form-label">Lng</label>
    <input type="number" step="any" name="lng" class="form-control">
  </div>

  <div class="col-md-4">
    <label class="form-label">{{ __('global.avatar') }} (max 240×240)</label>
    <input type="file" name="avatar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
    <div class="form-text">{{ __('global.image_constraints_240') }}</div>
  </div>
  <div class="col-md-2">
    <img data-avatar-preview src="https://via.placeholder.com/240?text=Avatar"
         class="img-thumbnail" style="max-width:100%;max-height:120px;object-fit:cover;">
  </div>
</div>
