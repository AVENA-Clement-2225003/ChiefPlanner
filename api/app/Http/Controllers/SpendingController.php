<?php

namespace App\Http\Controllers;

use App\Models\Spending;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpendingController extends Controller
{
    private const WEEKS_SHOWN = 7;

    /** The month so far, compared with the previous month, and the last weeks one by one. */
    public function summary(Request $request): JsonResponse
    {
        $today = CarbonImmutable::today();
        $total = fn (CarbonImmutable $from, CarbonImmutable $to) => (float) Spending::query()
            ->where('user_id', $request->user()->id)->whereBetween('spent_on', [$from, $to])->sum('amount');

        $monthTotal = $total($today->startOfMonth(), $today);
        $previousMonth = $total($today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth());

        $points = [];
        foreach (range(self::WEEKS_SHOWN - 1, 0) as $weeksAgo) {
            $start = $today->startOfWeek()->subWeeks($weeksAgo);
            $points[] = ['label' => 'S'.$start->isoWeek(), 'amount' => $total($start, $start->endOfWeek())];
        }

        return response()->json([
            'monthTotal' => $monthTotal,
            'currency' => 'EUR',
            // Null when there is nothing to compare with.
            'variationPercent' => $previousMonth > 0 ? (int) round(($monthTotal - $previousMonth) / $previousMonth * 100) : null,
            'points' => $points,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'spentOn' => ['nullable', 'date'],
        ]);

        $spending = Spending::create([
            'user_id' => $request->user()->id,
            'amount' => $data['amount'],
            'spent_on' => $data['spentOn'] ?? CarbonImmutable::today(),
        ]);

        return response()->json(['id' => $spending->id, 'amount' => $spending->amount, 'spentOn' => $spending->spent_on->toDateString()], 201);
    }

    public function destroy(Request $request, Spending $spending): JsonResponse
    {
        abort_unless($spending->user_id === $request->user()->id, 403);
        $spending->delete();

        return response()->json(null, 204);
    }
}
