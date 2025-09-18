<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name','model','color','size','price','notes','complements','photo_path',
    ];

    protected $casts = [
        'price'       => 'float',
        'complements' => 'array',
        'photo_path'  => 'array',
    ];

    /* ----------------- MUTATOR: SEMPRE SALVA COMO ARRAY JSON ----------------- */
    public function setComplementsAttribute($value): void
    {
        // Como há cast 'array', gravamos o ARRAY normalizado,
        // evitando json_encode aqui para não duplicar/estragar o cast.
        $this->attributes['complements'] = $this->normalizeToArray($value);
    }

    /* --------------- ACCESSOR: TEXTO PARA A UI ("a; b; c") ------------------ */
    public function getComplementsTextAttribute(): string
    {
        $arr = $this->complements; // já vem castado (array|null)
        if (!is_array($arr)) $arr = [];
        $arr = array_values(array_filter(array_map('trim', $arr), fn($v) => $v !== ''));
        return implode('; ', $arr);
    }

    /* --------------------------- HELPERS ------------------------------------- */
    private function normalizeToArray(null|string|array $in): array
    {
        if ($in === null) return [];                    // vazio -> []
        if (is_array($in)) {
            $parts = $in;
        } else {
            // string: aceita ; , ou quebras de linha
            $parts = preg_split('/[;,\r\n]+/', (string)$in) ?: [];
        }

        $parts = array_map(fn($v) => trim((string)$v), $parts);
        $parts = array_filter($parts, fn($v) => $v !== '');
        $parts = array_values(array_unique($parts));

        // Sempre devolve array (inclusive vazio) -> DB grava [] por causa do cast
        return $parts;
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'product_id');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'product_id');
    }

    protected function photoPath(): Attribute
    {
        // Blindagem: garante SEMPRE array.
        // - null/'' => []
        // - string JSON válida => decodifica
        // - string simples => [string]
        // - array => normaliza e remove vazios
        return Attribute::make(
            get: function ($value) {
                if (is_array($value)) {
                    return array_values(array_filter($value, fn ($v) => (string)$v !== ''));
                }
                if ($value === null || $value === '') {
                    return [];
                }
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return array_values(array_filter($decoded, fn ($v) => (string)$v !== ''));
                    }
                    return [$value];
                }
                return [];
            }
        );
    }
}
