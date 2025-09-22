@php
  // helper p/ primeira foto de product (photo_path JSON: ["products/..","..."])
  $firstPhoto = function ($product) {
    if (!$product || empty($product->photo_path)) return null;
    $arr = is_array($product->photo_path)
      ? $product->photo_path
      : (is_string($product->photo_path) ? (json_decode($product->photo_path, true) ?: []) : []);
    return $arr[0] ?? null;
  };
@endphp

@forelse ($items as $s)
@php
  $cust = $s->customer;
  $custAvatar = $cust && $cust->avatar_path ? asset($cust->avatar_path) : 'https://via.placeholder.com/120?text=Avatar';
@endphp
<tr>
  {{-- avatar do cliente --}}
  <td>
    <img src="{{ $custAvatar }}" class="img-thumbnail" style="width:80px;height:80px;object-fit:cover;" alt="avatar">
  </td>

  <td>{{ $cust?->name ?? '-' }}</td>
  <td>
    @php
      $badge = match ($s->status) {
        'fechado' => 'bg-success',
        'atrasado' => 'bg-warning text-dark',
        default => 'bg-secondary'
      };
    @endphp
    <span class="badge {{ $badge }}">{{ ucfirst($s->status) }}</span>
  </td>

  <td class="text-end">
    {{ isset($s->total) ? number_format($s->total, 2, ',', '.') : '-' }}
  </td>

  <td>{{ $s->created_at?->format('d/m/Y H:i') }}</td>

  <td class="text-end">
    <button type="button"
            class="btn btn-sm pm-btn pm-btn-dark btn-sale-details"
            data-id="{{ $s->id }}"
            data-bs-toggle="tooltip"
            title="{{ __('global.details') }}">
      <i class="bi bi-eye"></i>
    </button>

    @php $canDelete = ($s->status === 'fechado'); @endphp
    @if($canDelete)
      <form method="POST" action="{{ route('admin.sales.destroy', $s) }}" class="d-inline delete-form">
        @csrf @method('DELETE')
        <button class="btn btn-sm pm-btn pm-btn-primary"
                data-bs-toggle="tooltip"
                title="{{ __('global.delete') }}">
          <i class="bi bi-trash"></i>
        </button>
      </form>
    @endif
  </td>
</tr>
@empty
<tr>
  <td colspan="7" class="text-center text-muted">{{ __('global.no_results') }}</td>
</tr>
@endforelse
