<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $base = [
            ['name'=>'José Antônio da Silva','street'=>'Rua das Baraúnas','number'=>'45','district'=>'Centro','city'=>'Lagoa do Algodão','reference_point'=>'Perto do mercadinho São João','rg'=>'8192233-2','cpf'=>'013.456.789-01','phone'=>'(81) 98800-1001','other_contact'=>null,'lat'=>-8.282100,'lng'=>-36.000100],
            ['name'=>'Maria do Carmo Barbosa','street'=>'Sítio Riacho Doce','number'=>'s/n','district'=>'Zona Rural','city'=>'Serrote de Dentro','reference_point'=>'Posto de gasolina antigo','rg'=>'7732211-5','cpf'=>'114.567.890-12','phone'=>'(81) 98800-1002','other_contact'=>'(81) 99666-0001','lat'=>-8.301000,'lng'=>-36.012000],
            ['name'=>'Raimundo Nonato Ferreira','street'=>'Av. Padre Cícero','number'=>'320','district'=>'São Miguel','city'=>'Brejo do Capibaribe','reference_point'=>null,'rg'=>'5521988-0','cpf'=>'215.678.901-23','phone'=>'(81) 98800-1003','other_contact'=>null,'lat'=>-8.320000,'lng'=>-36.021000],
            ['name'=>'Ana Cláudia de Souza','street'=>'Rua da Algodoeira','number'=>'12','district'=>'Industrial','city'=>'Poço Fundo','reference_point'=>'Atrás da escola municipal','rg'=>'6123456-7','cpf'=>'316.789.012-34','phone'=>'(81) 98800-1004','other_contact'=>null,'lat'=>-8.330500,'lng'=>-36.031200],
            ['name'=>'Sebastião Oliveira','street'=>'Sítio Volta do Rio','number'=>'s/n','district'=>'Zona Rural','city'=>'Jucá Torto','reference_point'=>'Casa da sogra azul','rg'=>'7456123-1','cpf'=>'417.890.123-45','phone'=>'(81) 98800-1005','other_contact'=>null,'lat'=>-8.345000,'lng'=>-36.041000],
            ['name'=>'Luciana Batista','street'=>'Rua do Açude Novo','number'=>'201','district'=>'Boa Vista','city'=>'Taquara Velha','reference_point'=>null,'rg'=>'8899001-2','cpf'=>'518.901.234-56','phone'=>'(81) 98800-1006','other_contact'=>'(81) 98777-4444','lat'=>-8.355500,'lng'=>-36.051500],
            ['name'=>'Edvaldo Gomes','street'=>'Rua do Cedro','number'=>'88','district'=>'Centro','city'=>'Riacho Seco','reference_point'=>'Ao lado da borracharia','rg'=>'9011223-4','cpf'=>'619.012.345-67','phone'=>'(81) 98800-1007','other_contact'=>null,'lat'=>-8.361000,'lng'=>-36.061000],
            ['name'=>'Patrícia Nunes','street'=>'Travessa Santa Luzia','number'=>'34','district'=>'Santo Antônio','city'=>'Ladeira Grande','reference_point'=>null,'rg'=>'1122334-5','cpf'=>'720.123.456-78','phone'=>'(81) 98800-1008','other_contact'=>null,'lat'=>-8.372000,'lng'=>-36.071000],
            ['name'=>'Francisco de Assis','street'=>'Sítio Curral Novo','number'=>'s/n','district'=>'Zona Rural','city'=>'Cacimba Salgada','reference_point'=>'Bar do irmão','rg'=>'2233445-6','cpf'=>'821.234.567-89','phone'=>'(81) 98800-1009','other_contact'=>null,'lat'=>-8.380000,'lng'=>-36.081000],
            ['name'=>'Joana Darc Alves','street'=>'Rua Monte Alegre','number'=>'90','district'=>'Bela Vista','city'=>'Serra do Jardim','reference_point'=>null,'rg'=>'3344556-7','cpf'=>'932.345.678-90','phone'=>'(81) 98800-1010','other_contact'=>null,'lat'=>-8.390000,'lng'=>-36.091000],
            // +20 clientes (variações simples)
        ];

        // completar até 30 com padrões fixos
        for ($i = 10; $i < 30; $i++) {
            $base[] = [
                'name'=>"Cliente {$i} do Interior",
                'street'=>'Rua Projetada',
                'number'=>strval(50+$i),
                'district'=>'Bairro Novo',
                'city'=>'Vila Nova do Agreste',
                'reference_point'=>$i%3==0?'Cobrar no posto de gasolina':null,
                'rg'=>sprintf('%07d-%d', 5000000+$i, $i%9),
                'cpf'=>sprintf('%03d.%03d.%03d-%02d', 100+$i, 200+$i, 300+$i, $i%97),
                'phone'=>"(81) 98800-1".str_pad($i, 3, '0', STR_PAD_LEFT),
                'other_contact'=> $i%4==0 ? "(81) 98777-1".str_pad($i,3,'0',STR_PAD_LEFT) : null,
                'lat'=>-8.40 - ($i*0.001),
                'lng'=>-36.10 - ($i*0.001),
            ];
        }

        foreach ($base as $c) {
            Customer::updateOrCreate(
                ['name'=>$c['name'],'rg'=>$c['rg']],
                $c
            );
        }
    }
}
