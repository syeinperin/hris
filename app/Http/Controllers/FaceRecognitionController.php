<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema as DbSchema;
use App\Models\FaceTemplate;
use App\Models\Employee;

class FaceRecognitionController extends Controller
{
    // Lower = stricter, Higher = more tolerant
    private const MATCH_THRESHOLD = 0.45;

    /** Fallback if something calls /face */
    public function index() {
    // show the Face Recognition hub page with the two big buttons
    return view('face.index');
}

    /** Admin/HR enrollment page (keep as-is if you want) */
    public function enroll()
    {
        $query = Employee::query();
        if (DbSchema::hasColumn('employees', 'status')) {
            $query->where('status', 'active');
        }
        $employees = $query->orderBy('last_name')->orderBy('first_name')
            ->get(['id','employee_code','first_name','last_name']);

        $templates = FaceTemplate::with('employee')->latest()->get();

        return view('face.enroll', compact('employees','templates'));
    }

    /** Save descriptor + optional snapshot */
    public function enrollStore(Request $request)
    {
        // If descriptor arrived as JSON string, decode it
        if (is_string($request->input('descriptor'))) {
            $decoded = json_decode($request->input('descriptor'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['descriptor' => $decoded]);
            }
        }

        $v = Validator::make($request->all(), [
            'employee_id'  => 'required|exists:employees,id',
            'descriptor'   => 'required|array|size:128',
            'image_base64' => 'nullable|string',
        ]);

        $v->after(function ($validator) use ($request) {
            $desc = $request->input('descriptor');
            if (!is_array($desc) || count($desc) !== 128) {
                $validator->errors()->add('descriptor', 'Descriptor must be an array of 128 values.');
                return;
            }
            foreach ($desc as $i => $val) {
                if (!is_numeric($val)) {
                    $validator->errors()->add('descriptor', "Descriptor index {$i} must be numeric.");
                    break;
                }
            }
        });

        $v->validate();

        $imagePath = null;
        if ($request->filled('image_base64')) {
            $imagePath = $this->saveBase64Image((string)$request->input('image_base64'), 'faces');
        }

        FaceTemplate::create([
            'employee_id' => (int)$request->input('employee_id'),
            'descriptor'  => array_map('floatval', $request->input('descriptor')),
            'image_path'  => $imagePath,
        ]);

        return back()->with('success', 'Face template saved.');
    }

    /** Internal app face attendance (if you keep it) */
    public function attendance() { return view('face.attendance'); }

    /** NEW: Public kiosk face attendance page (no sidebar, no auth) */
    public function kiosk()
    {
        return view('kiosk.face'); // standalone HTML; see view file below
    }

    /** Match API (used by both internal page and kiosk page) */
    public function match(Request $request)
    {
        $data = $request->validate([
            'descriptor' => 'required|array|size:128',
        ]);

        $probe = array_map('floatval', $data['descriptor']);

        $templates = FaceTemplate::with('employee')->get();
        if ($templates->isEmpty()) {
            return response()->json([
                'matched'  => false,
                'distance' => null,
                'employee' => null,
            ]);
        }

        $best = null;
        foreach ($templates as $tpl) {
            $dist = $this->euclidean($probe, $tpl->descriptor);
            if ($best === null || $dist < $best['distance']) {
                $best = ['distance' => $dist, 'template' => $tpl];
            }
        }

        $isMatch = $best['distance'] <= self::MATCH_THRESHOLD;

        return response()->json([
            'matched'  => $isMatch,
            'distance' => round($best['distance'], 4),
            'employee' => $isMatch ? [
                'id'            => $best['template']->employee->id,
                'name'          => trim(($best['template']->employee->first_name ?? '').' '.($best['template']->employee->last_name ?? '')),
                'employee_code' => $best['template']->employee->employee_code,
            ] : null,
        ]);
    }

    /** Delete stored template */
    public function destroy(FaceTemplate $template)
    {
        if ($template->image_path) {
            Storage::disk('public')->delete($template->image_path);
        }
        $template->delete();

        return back()->with('success', 'Template removed.');
    }

    // ---------- helpers ----------
    private function euclidean(array $a, array $b): float
    {
        $sum = 0.0; $n = count($a);
        for ($i = 0; $i < $n; $i++) {
            $d = ($a[$i] ?? 0.0) - ($b[$i] ?? 0.0);
            $sum += $d * $d;
        }
        return sqrt($sum);
    }

    private function saveBase64Image(string $dataUri, string $folder): ?string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $dataUri, $m)) return null;
        $ext  = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
        $data = substr($dataUri, strpos($dataUri, ',') + 1);
        $bin  = base64_decode($data);
        $name = $folder.'/'.date('Ymd_His').'_'.bin2hex(random_bytes(4)).'.'.$ext;
        Storage::disk('public')->put($name, $bin);
        return $name;
    }
}
