@php
  $PLACEHOLDER = 'https://via.placeholder.com/240?text=Sem+Imagem';
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
    <label class="form-label">{{ __('global.lat') }}</label>
    <input type="number" step="any" name="lat" class="form-control">
  </div>
  <div class="col-md-2">
    <label class="form-label">{{ __('global.lng') }}</label>
    <input type="number" step="any" name="lng" class="form-control">
  </div>

  {{-- Upload do avatar (editável) + preview clicável --}}
  <div class="col-md-4">
    <label class="form-label">{{ __('global.avatar') }} (max 240×240)</label>
    <input type="file" name="avatar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
    <div class="form-text">{{ __('global.image_constraints_240') }}</div>
    @error('avatar')
      <div class="text-danger small">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-2">
    <label class="form-label d-block">{{ __('global.preview') }}</label>
    <img data-avatar-preview
         src="{{ $PLACEHOLDER }}"
         class="img-thumbnail cursor-pointer"
         style="max-width:100%;max-height:120px;object-fit:cover;"
         title="{{ __('global.click_to_zoom') }}">
    <div class="text-muted small mt-1">{{ __('global.click_to_zoom') }}</div>
  </div>

  {{-- Preview do local (somente visualização) --}}
  <div class="col-md-2">
    <label class="form-label d-block">{{ __('global.place_photo') }}</label>
    <img data-place-preview
         src="{{ $PLACEHOLDER }}"
         class="img-thumbnail cursor-pointer"
         style="max-width:100%;max-height:120px;object-fit:cover;"
         title="{{ __('global.click_to_zoom') }}">
  </div>

  {{-- Link para Google Maps --}}
  <div class="col-md-4 d-flex align-items-end">
    <a data-maps-link href="#" target="_blank" class="link-primary d-inline-flex align-items-center gap-1">
      <i class="bi bi-geo-alt"></i> <span>{{ __('global.click_to_view_on_map') }}</span>
    </a>
  </div>
</div>
