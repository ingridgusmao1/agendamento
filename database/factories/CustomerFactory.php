<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
    return [
        'name'=>'Cliente Fixo',
        'street'=>'Rua Fixa', 'number'=>'100',
        'district'=>'Centro', 'city'=>'Cidade Fictícia',
        'reference_point'=>null,'rg'=>'1234567-0','cpf'=>'000.111.222-33',
        'phone'=>'(81) 98800-0000','other_contact'=>null,
        'lat'=>-8.30,'lng'=>-36.00,
    ];
    }
}
