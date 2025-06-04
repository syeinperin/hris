<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PerformanceEvaluation;
use Symfony\Component\HttpFoundation\Response;

class EmployeeEvaluationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Guard: must have an Employee record
        if (! $user->employee) {
            abort(Response::HTTP_FORBIDDEN, 'No employee record found for your account.');
        }

        $employee = $user->employee;

        $evaluations = PerformanceEvaluation::with('form')
            ->where('employee_id', $employee->id)
            ->paginate(10);

        return view('my_evaluations.index', compact('evaluations'));
    }

    public function show(PerformanceEvaluation $evaluation)
    {
        $user = auth()->user();

        // Guard: must have an Employee record
        if (! $user->employee) {
            abort(Response::HTTP_FORBIDDEN, 'No employee record found for your account.');
        }

        // Guard: only allow viewing your own evaluations
        if ($evaluation->employee_id !== $user->employee->id) {
            abort(Response::HTTP_FORBIDDEN, 'You are not authorized to view this evaluation.');
        }

        $details = $evaluation->details()->with('criterion')->get();

        return view('my_evaluations.show', compact('evaluation', 'details'));
    }
}
