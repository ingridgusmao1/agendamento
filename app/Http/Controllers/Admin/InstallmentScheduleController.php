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
        $origin = $request->query('origin', 'all');
        if (!in_array($origin, ['all','store','external'], true)) {
            $origin = 'all';
        }
        $filters = ['origin' => $origin];

        $installments = $service->getFilteredInstallments($filters, self::PER_PAGE);

        $aggr   = $service->getStaticAggregates($filters);
        $counts = $aggr['counts'];
        $sums   = $aggr['sums'];

        return view('admin.installments_schedule.index', compact('installments','filters','counts','sums'));
    }
}
