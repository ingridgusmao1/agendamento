<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\SaleService;
use App\Http\Services\SaleReportService;
use App\Http\Services\PaymentService;
use App\Http\Validators\SaleValidator;
use App\Models\Sale;
use App\Models\Installment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SaleAdminController extends Controller
{
    private const PER_PAGE_DEFAULT = 15;
    private const PER_PAGE_MIN     = 5;
    private const PER_PAGE_MAX     = 100;

    public function __construct(
        private SaleService $service,
        private SaleReportService $reportService
    ) {}

    public function index()
    {
        return view('admin.sales.index');
    }

    public function fetch(Request $request): JsonResponse
    {
        $request->validate(SaleValidator::fetch());
        $q      = $this->service->qTrim($request->query('q', ''));
        $status = $request->query('status');
        $page   = max(1, (int) $request->query('page', 1));
        $perPage = self::PER_PAGE_DEFAULT;
        $payload = $this->service->fetch($q, $status, $page, $perPage);

        return response()->json([
            'html'     => (string) ($payload['html']     ?? ''),
            'total'    => (int)    ($payload['total']    ?? 0),
            'page'     => (int)    ($payload['page']     ?? $page),
            'perPage'  => (int)    ($payload['perPage']  ?? $perPage),
            'hasMore'  => (bool)   ($payload['hasMore']  ?? (($payload['total'] ?? 0) > ($page * $perPage))),
            'hasPrev'  =>          (($payload['page']    ?? $page) > 1),
            'hasNext'  => (bool)   ($payload['hasMore']  ?? (($payload['total'] ?? 0) > ($page * $perPage))),
        ]);
    }

    /** Retorna HTML do modal de detalhes */
    public function show(Sale $sale)
    {
        $html = $this->service->details($sale);
        return response()->json(['html' => $html]);
    }

    public function financialReports(Request $request)
    {
        $result = $this->reportService->list($request->all());

        return view('admin.financial_reports.index', [
            'sales'   => $result['sales'],
            'filters' => $result['filters'],
            'chips'   => $result['chips'],
            'options' => $result['options'],
            'payment_column'  => $result['payment_column'] ?? 'note',
            'totals'          => $result['totals'] ?? [],
        ]);
    }

    public function financialReportsPdf(Request $request)
    {
        // export() deve retornar: sales (Collection sem paginação),
        // totals['all'], chips, payment_column (o mesmo do index)
        $data = $this->reportService->export($request->all());

        $pdf = Pdf::loadView('admin.financial_reports.print', [
            'sales'           => $data['sales'],
            'chips'           => $data['chips'],
            'totals'          => $data['totals'],
            'payment_column'  => $data['payment_column'] ?? 'note',
        ])->setPaper('a4', 'landscape'); // paisagem fica melhor para tabelas largas

        return $pdf->stream('relatorio-financeiro-'.now()->format('Y-m-d_H-i').'.pdf');
    }

    public function paymentModal(Sale $sale, Installment $installment)
    {
        if ($installment->sale_id !== $sale->id) {
            abort(404);
        }

        $remaining = max(0, (float)$installment->amount - (float)$installment->paid_total);

        $paymentMethods = [
            'dinheiro' => 'Dinheiro',
            'pix'      => 'Pix',
            'credito'  => 'Crédito',
            'debito'   => 'Débito',
            'outro'    => 'Outro',
        ];

        return view('admin.sales.partials.payment_modal', compact(
            'sale', 'installment', 'remaining', 'paymentMethods'
        ));
    }

    public function storePayment(Request $request, Sale $sale, Installment $installment, PaymentService $payments)
    {
        if ($installment->sale_id !== $sale->id) {
            return response()->json(['ok' => false, 'message' => 'Parcela inválida'], 422);
        }

        // Normalizamos dados antes da validação dependente do restante
        $remaining = max(0, (float)$installment->amount - (float)$installment->paid_total);

        $data = $request->validate([
            'amount'         => ['required','numeric','min:0.01'],
            'payment_method' => ['required','in:dinheiro,pix,credito,debito,outro'],
            'note'           => ['nullable','string','max:500'],
        ]);

        if ($data['amount'] > $remaining + 0.00001) {
            return response()->json([
                'ok' => false,
                'message' => 'Valor acima do restante da parcela.',
            ], 422);
        }

        // Sempre vincula ao usuário autenticado
        $userId = Auth::id();

        // Realiza a criação do payment + atualização da parcela em transação
        $payments->createPaymentForInstallment(
            installment: $installment,
            userId: $userId,
            amount: (float)$data['amount'],
            method: $data['payment_method'],
            note: $data['note'] ?? null
        );

        return response()->json(['ok' => true]);
    }
}
