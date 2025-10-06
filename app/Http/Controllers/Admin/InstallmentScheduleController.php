<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\InstallmentScheduleService;
use Illuminate\Http\Request;

class InstallmentScheduleController extends Controller
{
    public function __construct(
        private readonly InstallmentScheduleService $service
    ) {}

    public function index(Request $request)
    {
        // valores aceitos: overdue | today | soon3 | soon5 | others | (null)
        $filter = $request->query('filter');

        $installments = $this->service->pendingChronologicalWithHighlight($filter);

        return view('admin.installments_schedule.index', compact('installments', 'filter'));
    }
}
