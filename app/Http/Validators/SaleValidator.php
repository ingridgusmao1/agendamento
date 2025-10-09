<?php

namespace App\Http\Validators;

final class SaleValidator
{
    /** validação do fetch (listagem com busca/paginação) */
    public static function fetch(): array
    {
        return [
            'q'        => ['nullable','string','max:200'],
            'page'     => ['nullable','integer','min:1'],
            'perPage'  => ['nullable','integer','min:1'],
            'per_page' => ['nullable','integer','min:1'],
            'status'   => ['nullable','in:aberto,atrasado,fechado'], // filtro opcional
        ];
    }
}
