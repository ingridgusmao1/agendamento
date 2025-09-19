<?php

namespace App\Http\Validators;

final class CustomerValidator
{
    /** Validação para listagem/fetch */
    public static function fetch(): array
    {
        return [
            'q'       => ['nullable','string','max:200'],
            'page'    => ['nullable','integer','min:1'],
            'perPage' => ['nullable','integer','min:1'],
            'per_page'=> ['nullable','integer','min:1'],
        ];
    }

    public static function rulesForStore(): array
    {
        return [
            'name'            => ['required','string','max:255'],
            'street'          => ['nullable','string','max:255'],
            'number'          => ['nullable','string','max:50'],
            'district'        => ['nullable','string','max:255'],
            'city'            => ['nullable','string','max:255'],
            'reference_point' => ['nullable','string','max:255'],
            'rg'              => ['nullable','string','max:50'],
            'cpf'             => ['nullable','string','max:20'],
            'phone'           => ['nullable','string','max:50'],
            'other_contact'   => ['nullable','string','max:255'],
            'lat'             => ['nullable','numeric'],
            'lng'             => ['nullable','numeric'],
            'avatar'          => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
        ];
    }

    public static function rulesForUpdate(int $customerId): array
    {
        return [
            'name'            => ['sometimes','required','string','max:255'],
            'street'          => ['sometimes','nullable','string','max:255'],
            'number'          => ['sometimes','nullable','string','max:50'],
            'district'        => ['sometimes','nullable','string','max:255'],
            'city'            => ['sometimes','nullable','string','max:255'],
            'reference_point' => ['sometimes','nullable','string','max:255'],
            'rg'              => ['sometimes','nullable','string','max:50'],
            'cpf'             => ['sometimes','nullable','string','max:20'],
            'phone'           => ['sometimes','nullable','string','max:50'],
            'other_contact'   => ['sometimes','nullable','string','max:255'],
            'lat'             => ['sometimes','nullable','numeric'],
            'lng'             => ['sometimes','nullable','numeric'],
            'avatar'          => ['sometimes','nullable','image','mimes:jpg,jpeg,png,webp'],
        ];
    }
}
