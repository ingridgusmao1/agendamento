<?php

namespace App\Http\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ProductPhotoService
{
    public function addPhoto(Product $product, UploadedFile $file): array
    {
        $paths = $product->photo_path ?? [];
        if (count($paths) >= 10) {
            abort(422, 'Limite de 10 fotos atingido.');
        }

        $normName = $this->normalize($product->name);
        $next = $this->nextOrder($paths, $product->id, $normName); // 1..10

        // Nome final .jpeg
        $filename = "{$product->id}{$normName}{$next}.jpeg";
        $relPath  = "products/{$filename}";

        // Garante conversão para JPEG (opcional: Intervention)
        // Se não quiser usar Intervention, use $file->storeAs('products', $filename, 'public');
        if (class_exists(Image::class)) {
            $img = Image::read($file->getPathname())->toJpeg(85);
            Storage::disk('public')->put($relPath, (string)$img);
        } else {
            // fallback: só armazena o que veio (renomeando extensão para .jpeg)
            $file->storeAs('products', $filename, ['disk' => 'public']);
        }

        $paths[] = $relPath;
        $product->photo_path = array_values($paths);
        $product->save();

        return $product->toArray();
    }

    public function removePhoto(Product $product, string $path): array
    {
        $paths = $product->photo_path ?? [];
        $new = array_values(array_filter($paths, fn($p) => $p !== $path));

        // Apaga o arquivo físico
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $product->photo_path = $new;
        $product->save();

        return $product->toArray();
    }

    private function normalize(string $name): string
    {
        $upper = Str::upper($name);
        $ascii = Str::ascii($upper);        // remove acentos
        $flat  = preg_replace('/[^A-Z0-9]/', '', $ascii) ?? '';
        return $flat;
    }

    private function nextOrder(array $currentPaths, int $id, string $norm): int
    {
        $max = 0;
        foreach ($currentPaths as $p) {
            $base = pathinfo($p, PATHINFO_BASENAME); // 10FOGAO5BOCAS3.jpeg
            $noExt = preg_replace('/\.jpe?g$/i', '', $base);
            $prefix = "{$id}{$norm}";
            if (Str::startsWith($noExt, $prefix)) {
                $n = (int) Str::after($noExt, $prefix);
                if ($n > $max) $max = $n;
            }
        }
        return $max + 1;
    }
}