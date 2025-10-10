<?php

namespace App\Http\Validators;

use Illuminate\Validation\Rule;

final class ProductValidator
{
    /** Validação para listagem/fetch */
    public static function fetch(): array
    {
        return [
            'q'    => ['nullable', 'string', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /** Criação (uso genérico pelo controller/serviço) */
    public static function store(): array
    {
        return [
            'name'         => ['required', 'string', 'max:160'],
            'model'        => ['nullable', 'string', 'max:160'],
            'size'         => ['nullable', 'string', 'max:100'],
            'price'        => ['required', 'numeric', 'min:0'],
            'notes'        => ['nullable', 'string'],
            'photo_path'   => ['nullable', 'string', 'max:255'],
            'complements'  => ['nullable', 'string'],
            'stock_total'  => ['required', 'integer'], // aceita negativos
        ];
    }

    /** Atualização (uso genérico pelo controller/serviço) */
    public static function update(): array
    {
        // mesmas regras do store neste caso
        return self::store();
    }

    /** Criação via formulário de produto (com suporte a múltiplas fotos) */
    public static function rulesForStore(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'model'        => ['nullable', 'string', 'max:255'],
            'size'         => ['nullable', 'string', 'max:255'],
            'price'        => ['required', 'numeric', 'min:0'],
            'notes'        => ['nullable', 'string'],
            'complements'  => ['nullable', 'string'],
            'stock_total'  => ['required', 'integer'], // aceita negativos
            'photos'       => ['nullable', 'array', 'max:10'],
            'photos.*'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:16384'], // 16 MB por arquivo
        ];
    }

    /** Edição via formulário de produto */
    public static function rulesForUpdate(int $productId): array
    {
        return [
            'name'         => ['sometimes', 'required', 'string', 'max:255'],
            'model'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'size'         => ['sometimes', 'nullable', 'string', 'max:255'],
            'price'        => ['sometimes', 'required', 'numeric', 'min:0'],
            'notes'        => ['sometimes', 'nullable', 'string'],
            'complements'  => ['sometimes', 'nullable', 'string'],
            'stock_total'  => ['sometimes', 'required', 'integer'], // aceita negativos
            'photos'       => ['sometimes', 'array'],
            'photos.*'     => ['file', 'mimes:jpg,jpeg,png,webp', 'max:8192'], // 8 MB por arquivo
        ];
    }

    /** Upload via galeria do produto */
    public static function galleryUpload(): array
    {
        return [
            'photos'   => ['required', 'array'],
            'photos.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:16384'],
        ];
    }

    /** Remoção via galeria do produto */
    public static function galleryDelete(): array
    {
        return [
            'indexes'   => ['required', 'array', 'min:1'],
            'indexes.*' => ['integer', 'min:0'],
        ];
    }
}
