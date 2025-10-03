<?php

namespace App\Http\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PhotoPathService
{
    public static function baseSlug(string $name): string
    {
        return Str::upper(Str::slug($name, '-'));
    }

    public static function filename(string $name, int $id, int $index = 1, string $ext = 'jpg'): string
    {
        $base = self::baseSlug($name);
        $suffix = $index > 1 ? "-{$index}" : '';
        return "{$base}-{$id}{$suffix}.{$ext}";
    }

    public static function dirFor(string $kind): string
    {
        return match ($kind) {
            'cliente' => 'customers',
            'lugar'   => 'places',
            default   => 'misc',
        };
    }

    public static function saveUploaded(string $kind, string $name, int $id, UploadedFile $file, int $index = 1): string
    {
        $dir = self::dirFor($kind);
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = self::filename($name, $id, $index, $ext);

        $targetDir = public_path($dir);     
        if (!is_dir($targetDir)) { @mkdir($targetDir, 0775, true); }

        $file->move($targetDir, $filename);

        return "{$dir}/{$filename}";
    }

    // ===== NOVO: helpers para nome de "lugar" baseado em lat/lng =====
    public static function encodeCoord(float $value): string
    {
        $prefix = $value < 0 ? 'M' : 'P';
        $abs    = abs($value);
        $str    = number_format($abs, 6, '.', '');
        $digits = preg_replace('/[^0-9]/', '', $str) ?: '0';
        return $prefix . $digits;
    }

    public static function placeNameFromLatLng(?float $lat, ?float $lng): string
    {
        $latPart = self::encodeCoord($lat ?? 0.0);
        $lngPart = self::encodeCoord($lng ?? 0.0);
        return "{$latPart}-{$lngPart}";
    }
}