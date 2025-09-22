<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use Illuminate\Support\Str;
use App\Http\Actions\HousekeepingAction;

class CustomerSeeder extends Seeder
{
    public function __construct(private HousekeepingAction $hk) {}

    public function run(): void
    {
        // 1) Limpar pastas alvo
        $this->hk->cleanPublicFolder(public_path('customers'));

        // 2) Dados-base
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
        ];

        for ($i = 10; $i < 30; $i++) {
            $base[] = [
                'name'=>"Cliente {$i} do Interior",
                'street'=>'Rua Projetada',
                'number'=>(string)(50+$i),
                'district'=>'Bairro Novo',
                'city'=>'Vila Nova do Agreste',
                'reference_point'=>$i%3==0?'Cobrar no posto de gasolina':null,
                'rg'=>sprintf('%07d-%d', 5000000+$i, $i%9),
                'cpf'=>sprintf('%03d.%03d.%03d-%02d', 100+$i, 200+$i, 300+$i, $i%97),
                'phone'=>"(81) 98800-1".str_pad((string)$i, 3, '0', STR_PAD_LEFT),
                'other_contact'=> $i%4==0 ? "(81) 98777-1".str_pad((string)$i,3,'0',STR_PAD_LEFT) : null,
                'lat'=>-8.40 - ($i*0.001),
                'lng'=>-36.10 - ($i*0.001),
            ];
        }

        // 3) Criar/atualizar clientes (sem avatar_path ainda)
        $created = [];
        foreach ($base as $c) {
            $cust = Customer::updateOrCreate(
                ['name'=>$c['name'], 'rg'=>$c['rg']],
                $c
            );
            $created[] = $cust;
        }

        // 4) Gerar avatar para cada cliente e atualizar avatar_path
        $this->ensureDir(public_path('customers'));
        foreach ($created as $cust) {
            $filename = $this->customerFilename($cust->name, $cust->id); // e.g. CLIENTE-29-DO-INTERIOR-30.jpg
            $relPath  = 'customers/'.$filename;
            $absPath  = public_path($relPath);

            $this->generateAvatarJpeg($absPath, $seed = crc32($cust->name.$cust->id));
            $cust->forceFill(['avatar_path' => $relPath])->save();
        }
    }

    /** Gera nome do arquivo: NOME-EM-CAIXA-ALTA-ID.jpg */
    private function customerFilename(string $name, int $id): string
    {
        // slug ASCII e em maiúsculas, preservando hífens
        $slug = Str::slug($name, '-');   // ex: cliente-29-do-interior
        $slug = strtoupper($slug);       // ex: CLIENTE-29-DO-INTERIOR
        return "{$slug}-{$id}.jpg";
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }

    /** Remove diretório recursivamente (não usado por padrão) */
    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = array_diff(scandir($dir), ['.','..']);
        foreach ($items as $item) {
            $path = $dir.DIRECTORY_SEPARATOR.$item;
            is_dir($path) ? $this->rrmdir($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    /**
     * Gera um avatar JPG ~160x160 com formas geométricas aleatórias.
     * Usa GD (sem dependências). Tenta manter < 50 KB via qualidade.
     */
    private function generateAvatarJpeg(string $path, int $seed = 0): void
    {
        $w = 160; $h = 160;
        $img = imagecreatetruecolor($w, $h);

        // Fundo
        mt_srand($seed);
        $bg = imagecolorallocate($img, mt_rand(200,255), mt_rand(200,255), mt_rand(200,255));
        imagefilledrectangle($img, 0, 0, $w, $h, $bg);

        // Desenhar algumas formas simples
        $nShapes = 6;
        for ($i=0; $i<$nShapes; $i++) {
            $col = imagecolorallocate($img, mt_rand(40,200), mt_rand(40,200), mt_rand(40,200));
            $type = mt_rand(0, 2); // 0: retângulo, 1: elipse, 2: linhas
            switch ($type) {
                case 0: // retângulo
                    $x1 = mt_rand(0, $w-10); $y1 = mt_rand(0, $h-10);
                    $x2 = mt_rand($x1+5, min($x1+70, $w)); $y2 = mt_rand($y1+5, min($y1+70, $h));
                    imagefilledrectangle($img, $x1, $y1, $x2, $y2, $col);
                    break;
                case 1: // elipse
                    $cx = mt_rand(20, $w-20); $cy = mt_rand(20, $h-20);
                    $rx = mt_rand(10, 50); $ry = mt_rand(10, 50);
                    imagefilledellipse($img, $cx, $cy, $rx, $ry, $col);
                    break;
                case 2: // linhas
                    $x1 = mt_rand(0, $w); $y1 = mt_rand(0, $h);
                    $x2 = mt_rand(0, $w); $y2 = mt_rand(0, $h);
                    imagesetthickness($img, mt_rand(1, 4));
                    imageline($img, $x1, $y1, $x2, $y2, $col);
                    break;
            }
        }

        // ícone central (círculo) para dar identidade
        $accent = imagecolorallocate($img, mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
        imagefilledellipse($img, (int)($w/2), (int)($h/2), 64, 64, $accent);

        // Gravar JPEG com tentativa de <50KB: qualidade 70, ajusta se necessário
        $quality = 72;
        $tmp = $path.'.tmp';
        imagejpeg($img, $tmp, $quality);
        clearstatcache();
        $size = @filesize($tmp);

        // se passou de 50KB, reduzir qualidade em passos
        while ($size !== false && $size > 50 * 1024 && $quality > 40) {
            $quality -= 5;
            imagejpeg($img, $tmp, $quality);
            clearstatcache();
            $size = @filesize($tmp);
        }

        // mover para destino final
        @rename($tmp, $path);
        imagedestroy($img);
    }
}
