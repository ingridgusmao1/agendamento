<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;

class LookupController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $r)
    {
        $q = trim((string)$r->get('q',''));

        $by = $r->get('by','customer'); // 'customer' | 'nota'

        if($by==='nota'){
            $sales = Sale::with('customer:id,name')
            ->where('number','like',"%$q%")->limit(20)->get();
        } else {
            $sales = Sale::with('customer:id,name,rg,cpf')
            ->whereHas('customer', function($qq) use($q){
                $qq->where('name','like',"%$q%")
                ->orWhere('rg','like',"%$q%")
                ->orWhere('cpf','like',"%$q%");
            })->limit(20)->get();
        }

        return $sales;
    }
}
