<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;

class LookupController extends Controller
{
    public function __invoke(Request $r)
    {
        $q  = trim((string) $r->get('q', ''));
        $by = $r->get('by', 'customer'); // 'customer' | 'nota'

        // Parâmetros de paginação
        $all      = (int) $r->get('all', 0) === 1;
        $limitInp = (int) $r->get('limit', 0);
        $perPage  = (int) $r->get('per_page', 0);
        $page     = max(1, (int) $r->get('page', 1));

        // Escolhe o tamanho do lote
        // prioridade: limit > per_page > default(20)
        $size = $limitInp > 0 ? $limitInp : ($perPage > 0 ? $perPage : 20);
        // segurança para não estourar a memória se pedirem demais
        $size = max(1, min($size, 1000));

        // Query base
        $qbuilder = Sale::query();

        if ($by === 'nota') {
            $qbuilder->with('customer:id,name')
                     ->when($q !== '', fn($qq) => $qq->where('number', 'like', "%{$q}%"));
        } else {
            $qbuilder->with('customer:id,name,rg,cpf')
                     ->when($q !== '', function ($qq) use ($q) {
                         $qq->whereHas('customer', function ($qc) use ($q) {
                             $qc->where('name', 'like', "%{$q}%")
                                ->orWhere('rg',  'like', "%{$q}%")
                                ->orWhere('cpf', 'like', "%{$q}%");
                         });
                     });
        }

        // Ordenação previsível (ajuste se quiser por 'number' ou 'created_at')
        $qbuilder->orderBy('id', 'desc');

        if ($all) {
            // retorna tudo (sem paginação)
            return $qbuilder->get();
        }

        // Paginação manual por page/per_page OU limit/offset
        $offset = ($page - 1) * $size;

        $sales = $qbuilder
            ->skip($offset)
            ->take($size)
            ->get();

        return $sales;
    }
}
