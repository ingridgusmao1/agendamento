<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name'=>'Sofá 3 Lugares','model'=>'Comfort 300','color'=>'Cinza','size'=>'2.10m','price'=>1899.90,'notes'=>'Tecido suede','complements'=>['almofadas extras']],
            ['name'=>'Mesa de Jantar','model'=>'Serra Talhada','color'=>'Nogueira','size'=>'6 lugares','price'=>1299.00,'notes'=>null,'complements'=>['vidro 8mm']],
            ['name'=>'Cadeira Estofada','model'=>'Capibaribe','color'=>'Bege','size'=>'Única','price'=>199.00,'notes'=>null,'complements'=>[]],
            ['name'=>'Guarda-roupa 6 portas','model'=>'Agreste','color'=>'Branco','size'=>'2.30m','price'=>2199.00,'notes'=>'Com espelho','complements'=>[]],
            ['name'=>'Cama Box Casal','model'=>'Nordeste','color'=>'Preto','size'=>'138x188','price'=>999.90,'notes'=>'Mola ensacada','complements'=>[]],
            ['name'=>'Colchão Casal','model'=>'NanaFlex','color'=>'Branco','size'=>'138x188','price'=>799.00,'notes'=>'Densidade D33','complements'=>[]],
            ['name'=>'Rack para TV','model'=>'Taquara','color'=>'Amêndoa','size'=>'1.60m','price'=>489.00,'notes'=>null,'complements'=>[]],
            ['name'=>'Painel para TV','model'=>'Caruaru','color'=>'Canela','size'=>'55"','price'=>349.00,'notes'=>null,'complements'=>[]],
            ['name'=>'Geladeira Duplex','model'=>'Sertânia Frost','color'=>'Inox','size'=>'400L','price'=>3499.00,'notes'=>'Classe A+','complements'=>[]],
            ['name'=>'Fogão 5 bocas','model'=>'Santa Cruz','color'=>'Preto','size'=>'76L','price'=>1290.00,'notes'=>'Acendimento automático','complements'=>[]],
            ['name'=>'Micro-ondas','model'=>'Garanhuns 30','color'=>'Inox','size'=>'30L','price'=>649.00,'notes'=>null,'complements'=>[]],
            ['name'=>'Lavadora','model'=>'Bezerros 11','color'=>'Branco','size'=>'11kg','price'=>1799.00,'notes'=>null,'complements'=>[]],
            ['name'=>'Guarda-roupa 4 portas','model'=>'Vitória','color'=>'Amêndoa','size'=>'1.80m','price'=>1490.00,'notes'=>null,'complements'=>[]],
            ['name'=>'Mesa de Centro','model'=>'Brejo','color'=>'Nogueira','size'=>'90x60','price'=>259.00,'notes'=>null,'complements'=>[]],
            ['name'=>'Cômoda 5 gavetas','model'=>'Arcoverde','color'=>'Branco','size'=>'1.10m','price'=>799.00,'notes'=>null,'complements'=>[]],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(
                ['name'=>$p['name'],'model'=>$p['model']],
                [
                    'color'=>$p['color'],
                    'size'=>$p['size'],
                    'price'=>$p['price'],
                    'notes'=>$p['notes'],
                    'complements'=>$p['complements'],
                    'photo_path'=>null,
                ]
            );
        }
    }
}
