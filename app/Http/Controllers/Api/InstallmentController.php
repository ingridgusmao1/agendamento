<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\PaymentService;
use App\Models\Installment;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function pay(Request $request, Installment $installment)
    {
        $data = $request->validate([
            'amount' => ['required','numeric','min:0.01'],
            'note'   => ['nullable','string'],
        ]);

        PaymentService::pay($installment, (float)$data['amount'], $request->user()->id, $data['note'] ?? null);

        // Retorna a parcela atualizada e o saldo remanescente da venda
        $sale = $installment->sale()->with('installments')->first();
        $remaining = $sale->remainingBalance();

        return response()->json([
            'installment' => $installment->fresh(),
            'remaining'   => $remaining,
        ]);
    }
}
