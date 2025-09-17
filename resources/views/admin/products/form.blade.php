@extends('layouts.admin')
@section('title', $product ? 'Editar produto' : 'Novo produto')

@section('content')
<a href="{{ route('admin.products.index') }}" class="btn btn-link">&larr; Voltar</a>

<div class="card shadow-sm">
  <div class="card-body">
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ $product ? route('admin.products.update',$product) : route('admin.products.store') }}">
      @csrf
      @if($product) @method('PUT') @endif

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nome</label>
          <input type="text" name="name" class="form-control" required value="{{ old('name', $product->name ?? '') }}">
        </div>
        <div class="col-md-6">
          <label class="form-label">Modelo</label>
          <input type="text" name="model" class="form-control" value="{{ old('model', $product->model ?? '') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Cor</label>
          <input type="text" name="color" class="form-control" value="{{ old('color', $product->color ?? '') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Tamanho</label>
          <input type="text" name="size" class="form-control" value="{{ old('size', $product->size ?? '') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Preço</label>
          <input type="number" step="0.01" min="0" name="price" class="form-control" required value="{{ old('price', $product->price ?? '') }}">
        </div>
        <div class="col-12">
          <label class="form-label">Observações</label>
          <textarea name="notes" class="form-control" rows="3">{{ old('notes', $product->notes ?? '') }}</textarea>
        </div>
      </div>

      <div class="mt-3">
        <button class="btn btn-primary">{{ $product ? 'Salvar' : 'Criar' }}</button>
      </div>
    </form>
  </div>
</div>
@endsection
