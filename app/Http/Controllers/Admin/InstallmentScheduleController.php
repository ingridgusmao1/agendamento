<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\InstallmentScheduleService;
use Illuminate\Http\Request;

class InstallmentScheduleController extends Controller
{
    private const PER_PAGE = 50;

    public function index(Request $request, InstallmentScheduleService $service)
    {
        $filters = [
            'status' => $request->get('status'),
            'origins' => $request->get('origins', ['store', 'external']),
        ];

        $installments = $service->getFilteredInstallments($filters, self::PER_PAGE);

        return view('admin.installments_schedule.index', [
            'installments' => $installments,
            'filters' => $filters,
        ]);
    }
}
