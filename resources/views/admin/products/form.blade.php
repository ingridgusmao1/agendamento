@extends('layouts.admin')
@section('title', $product ? 'Editar produto' : 'Novo produto')

@section('content')
<a href="{{ route('admin.products.index') }}" class="pm-link d-inline-flex align-items-center mb-2">&larr; Voltar</a>

<div class="card shadow-sm pm-card">
  <div class="pm-card-body">
    @if ($errors->any())
      <div class="pm-alert mb-3">
        <ul class="mb-0">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form class="pm-form" method="POST" action="{{ $product ? route('admin.products.update',$product) : route('admin.products.store') }}">
      @csrf
      @if($product) @method('PUT') @endif

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nome</label>
          <input type="text" name="name" class="form-control pm-input" required value="{{ old('name', $product->name ?? '') }}">
        </div>
        <div class="col-md-6">
          <label class="form-label">Modelo</label>
          <input type="text" name="model" class="form-control pm-input" value="{{ old('model', $product->model ?? '') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Cor</label>
          <input type="text" name="color" class="form-control pm-input" value="{{ old('color', $product->color ?? '') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Tamanho</label>
          <input type="text" name="size" class="form-control pm-input" value="{{ old('size', $product->size ?? '') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Preço</label>
          <input type="number" step="0.01" min="0" name="price" class="form-control pm-input" required value="{{ old('price', $product->price ?? '') }}">
        </div>
        <div class="col-12">
          <label class="form-label">Observações</label>
          <textarea name="notes" class="form-control pm-input pm-textarea" rows="3">{{ old('notes', $product->notes ?? '') }}</textarea>
        </div>
      </div>

      <div class="mt-3 d-flex gap-2">
        <button class="btn pm-btn pm-btn-primary">{{ $product ? 'Salvar' : 'Criar' }}</button>
        <a href="{{ route('admin.products.index') }}" class="btn pm-btn pm-btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection
