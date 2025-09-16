<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
    return [
        'name' => 'Produto Fixo',
        'model'=> 'Modelo X',
        'color'=> 'Preto',
        'size' => 'Único',
        'price'=> 100.00,
        'notes'=> null,
        'complements'=>[],
        'photo_path'=>null,
    ];
    }
}
