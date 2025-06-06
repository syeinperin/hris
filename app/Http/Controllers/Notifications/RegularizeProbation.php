<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Employee;
use Carbon\Carbon;

class RegularizeProbation extends Controller
{
    /**
     * If you register a route to this controller,
     * visiting that route will run the same logic as the Artisan command:
     *  - Find all employees marked “probationary” whose probation_end_date ≤ today
     *  - Flip them to “regular”
     *  - Return a JSON summary
     *
     * Example (in routes/web.php):
     *   Route::get('/notify/regularize-probation', [RegularizeProbation::class, '__invoke']);
     */
    public function __invoke(): JsonResponse
    {
        $today = Carbon::today();

        $toBeRegularized = Employee::where('employment_type', 'probationary')
            ->whereNotNull('probation_end_date')
            ->whereDate('probation_end_date', '<=', $today)
            ->get();

        if ($toBeRegularized->isEmpty()) {
            return response()->json([
                'message' => 'No probationary employees to regularize today.',
                'updated' => [],
            ], 200);
        }

        $updatedNames = [];
        foreach ($toBeRegularized as $employee) {
            $employee->update([
                'employment_type'   => 'regular',
                'probation_end_date'=> null,
            ]);
            $updatedNames[] = $employee->name;
        }

        return response()->json([
            'message' => 'Probationary employees have been regularized.',
            'updated' => $updatedNames,
        ], 200);
    }
}
