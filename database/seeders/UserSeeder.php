<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->delete();

        // Admin permanece com store_mode = null
        User::create([
            'name'       => 'Administrador',
            'email'      => 'admin@admin.com',
            'password'   => Hash::make('password'),
            'code'       => 'ADM001',
            'type'       => 'admin',
            'store_mode' => null,
        ]);

        // Vendedores da loja
        User::create([
            'name'       => 'Carlos Vendas Loja',
            'email'      => 'carlos@loja.com',
            'password'   => Hash::make('vendedor123'),
            'code'       => 'VEN001',
            'type'       => 'vendedor',
            'store_mode' => 'loja',
        ]);

        User::create([
            'name'       => 'Mariana Caixa',
            'email'      => 'mariana@loja.com',
            'password'   => Hash::make('vendedor123'),
            'code'       => 'VEN002',
            'type'       => 'vendedor',
            'store_mode' => 'loja',
        ]);

        // Vendedor híbrido (atua em loja e externo)
        User::create([
            'name'       => 'Henrique Híbrido',
            'email'      => 'henrique@ambos.com',
            'password'   => Hash::make('vendedor123'),
            'code'       => 'VEN003',
            'type'       => 'vendedor',
            'store_mode' => 'ambos',
        ]);

        // Vendedores externos
        User::create([
            'name'       => 'João Externo',
            'email'      => 'joao@externo.com',
            'password'   => Hash::make('vendedor123'),
            'code'       => 'VEN004',
            'type'       => 'vendedor',
            'store_mode' => 'externo',
        ]);

        User::create([
            'name'       => 'Luciana Campos',
            'email'      => 'luciana@externo.com',
            'password'   => Hash::make('vendedor123'),
            'code'       => 'VEN005',
            'type'       => 'vendedor',
            'store_mode' => 'externo',
        ]);

        // Vendedores cobradores (sempre ambos)
        User::create([
            'name'       => 'Roberto Cobrador',
            'email'      => 'roberto@ambos.com',
            'password'   => Hash::make('vendedor123'),
            'code'       => 'VEN006',
            'type'       => 'vendedor_cobrador',
            'store_mode' => 'ambos',
        ]);

        User::create([
            'name'       => 'Fernanda Atendimento',
            'email'      => 'fernanda@ambos.com',
            'password'   => Hash::make('vendedor123'),
            'code'       => 'VEN007',
            'type'       => 'vendedor_cobrador',
            'store_mode' => 'ambos',
        ]);
    }
}
