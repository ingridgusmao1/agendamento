<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin principal
        User::updateOrCreate(
            ['code'=>'ADM001'],
            ['name'=>'Administrador Geral','email'=>'adm001@local.test','type'=>'admin','store_mode' => null,'password'=>Hash::make('password')]
        );

        // 6 funcionários (codes fixos)
        $users = [
            ['code'=>'VEN101','name'=>'João Silva','type'=>'vendedor','store_mode'=>'loja'],
            ['code'=>'VEN102','name'=>'Maria Souza','type'=>'vendedor','store_mode'=>'externo'],
            ['code'=>'COB201','name'=>'Pedro Lopes','type'=>'cobrador','store_mode'=>'outro'],
            ['code'=>'COB202','name'=>'Aline Santos','type'=>'cobrador','store_mode'=>'loja'],
            ['code'=>'HIB301','name'=>'Carlos Queiroz','type'=>'vendedor_cobrador','store_mode'=>'loja'],
            ['code'=>'HIB302','name'=>'Beatriz Oliveira','type'=>'vendedor_cobrador','store_mode'=>'ambos'],
        ];
        foreach ($users as $u) {
            User::updateOrCreate(
                ['code' => $u['code']],
                [
                    'name'       => $u['name'],
                    'email'      => strtolower($u['code']).'@local.test',
                    'type'       => $u['type'],
                    'store_mode' => $u['store_mode'],
                    'password'   => Hash::make('password'),
                ]
            );
        }
    }
}
