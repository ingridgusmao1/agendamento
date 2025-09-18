<?php

namespace App\Http\Validators;

use Illuminate\Validation\Rule;

final class UserValidator
{
    public const TYPES = ['admin','vendedor','cobrador','vendedor_cobrador'];

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
            'code'     => ['required','string','max:50','unique:users,code'],
            'name'     => ['required','string','max:120'],
            'type'     => ['required', Rule::in(self::TYPES)],
            'password' => ['required','string','min:4'],
        ];
    }

    /** Atualização */
    public static function update(int $userId): array
    {
        return [
            'name'     => ['required','string','max:120'],
            'type'     => ['required', Rule::in(self::TYPES)],
            'password' => ['nullable','string','min:4'],
        ];
    }

    /** Reset de senha */
    public static function resetPassword(): array
    {
        return [
            'password' => ['required','string','min:4'],
        ];
    }
}
