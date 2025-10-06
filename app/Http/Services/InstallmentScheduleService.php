<?php

namespace App\Http\Services;

use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InstallmentScheduleService
{
    public const OVERDUE = 'overdue';
    public const TODAY   = 'today';
    public const SOON_3  = 'soon3';
    public const SOON_5  = 'soon5';
    public const NONE    = 'none'; // >5 dias

    /**
     * $filter: null (todos) | overdue | today | soon3 | soon5 | others
     */
    public function pendingChronologicalWithHighlight(?string $filter = null, ?Carbon $referenceDate = null): Collection
    {
        $today = $referenceDate?->clone()->startOfDay() ?? Carbon::today();

        $items = Installment::with(['sale.customer'])
            ->where('status', '!=', 'pago')
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function (Installment $i) use ($today) {
                $i->setAttribute('highlight', $this->classifyByDueDate($i->due_date, $today));
                return $i;
            });

        if ($filter) {
            $map = [
                'overdue' => self::OVERDUE,
                'today'   => self::TODAY,
                'soon3'   => self::SOON_3,
                'soon5'   => self::SOON_5,
                'others'  => self::NONE,
            ];
            if (isset($map[$filter])) {
                $items = $items->where('highlight', $map[$filter]);
            }
        }

        return $items;
    }

    private function classifyByDueDate($dueDate, Carbon $today): string
    {
        $due  = $dueDate instanceof Carbon ? $dueDate->clone()->startOfDay() : Carbon::parse($dueDate)->startOfDay();
        $diff = $today->diffInDays($due, false);

        if ($diff < 0)   return self::OVERDUE;
        if ($diff === 0) return self::TODAY;
        if ($diff <= 3)  return self::SOON_3;
        if ($diff <= 5)  return self::SOON_5;
        return self::NONE;
    }
}
