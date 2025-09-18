<?php

namespace App\Http\Validators;

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
}
