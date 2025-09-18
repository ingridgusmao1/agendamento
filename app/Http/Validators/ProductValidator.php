<?php

namespace App\Http\Validators;
use Illuminate\Validation\Rule;

final class ProductValidator
{
    /** Validação para listagem/fetch */
    public static function fetch(): array
    {
        return [
            'q'    => ['nullable','string','max:200'],
            'page' => ['nullable','integer','min:1'],
        ];
    }

    /** Criação */
    public static function store(): array
    {
        return [
            'name'        => ['required','string','max:160'],
            'model'       => ['nullable','string','max:160'],
            'color'       => ['nullable','string','max:100'],
            'size'        => ['nullable','string','max:100'],
            'price'       => ['required','numeric','min:0'],
            'notes'       => ['nullable','string'],
            'photo_path'  => ['nullable','string','max:255'],
            'complements' => ['nullable','string'],
        ];
    }

    /** Atualização */
    public static function update(): array
    {
        // mesmas regras do store neste caso
        return self::store();
    }

    public static function rulesForStore(): array
    {
        return [
            'name'        => ['required','string','max:255'],
            'model'       => ['nullable','string','max:255'],
            'color'       => ['nullable','string','max:255'],
            'size'        => ['nullable','string','max:255'],
            'price'       => ['required','numeric','min:0'],
            'notes'       => ['nullable','string'],
            'complements' => ['nullable','string'],

            // novas regras de upload
            'photos'      => ['nullable','array','max:10'],
            'photos.*'    => ['file','mimes:jpg,jpeg,png,webp','max:8192'], // 8MB por arquivo
        ];
    }

    public static function rulesForUpdate(int $productId): array
    {
        return [
            'name'        => ['sometimes','required','string','max:255'],
            'model'       => ['sometimes','nullable','string','max:255'],
            'color'       => ['sometimes','nullable','string','max:255'],
            'size'        => ['sometimes','nullable','string','max:255'],
            'price'       => ['sometimes','required','numeric','min:0'],
            'notes'       => ['sometimes','nullable','string'],
            'complements' => ['sometimes','nullable','string'],

            'photos'      => ['sometimes','array'],
            'photos.*'    => ['file','mimes:jpg,jpeg,png,webp','max:8192'],
        ];
    }

}
