@php
  use Illuminate\Support\Facades\Storage;

  $MUGSHOT = asset('storage/customers/mugshot/mugshot.png');

  // Mantive seu helper; não é usado aqui, mas deixei para consistência
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
  $cust = $s->customer ?? null;

  // Caminho relativo vindo do banco
  $avatarRel = $cust?->avatar_path ?: null;

  // Checagem robusta de existência no disco "public"
  $avatarExists = false;
  if ($avatarRel) {
    try {
      $disk = Storage::disk('public');
      $avatarExists = $disk->exists($avatarRel);
      if (!$avatarExists) {
        // Em alguns ambientes (ex.: macOS), exists() pode falhar em paths peculiares;
        // conferimos também via caminho absoluto.
        $abs = $disk->path($avatarRel);
        $avatarExists = is_file($abs);
      }
    } catch (\Throwable $e) {
      $avatarExists = false;
    }
  }

  // URL final
  $avatarUrl = $avatarExists ? asset('storage/'.$avatarRel) : $MUGSHOT;

  // Badge de status (igual ao seu)
  $badge = match ($s->status) {
    'fechado'  => 'bg-success',
    'atrasado' => 'bg-warning text-dark',
    default    => 'bg-secondary'
  };
@endphp

<tr>
  {{-- avatar do cliente --}}
  <td style="width:120px">
    <img src="{{ $avatarUrl }}" alt="avatar" class="img-thumbnail"
         style="width:120px;height:120px;object-fit:cover;"
         onerror="this.onerror=null;this.src='{{ $MUGSHOT }}';">
  </td>

  <td>{{ $cust?->name ?? '-' }}</td>

  <td>{{ $s->number ?? '-' }}</td>

  <td>
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
  </td>
</tr>
@empty
<tr>
  <td colspan="7" class="text-center text-muted">{{ __('global.no_results') }}</td>
</tr>
@endforelse
