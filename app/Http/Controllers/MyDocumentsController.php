<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\DocumentStoreRequest;
use App\Http\Requests\DocumentUpdateRequest;

class MyDocumentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $employee = auth()->user()->employee;
        abort_unless($employee, 403, 'Employee profile required.');

        $docs = Document::mine($employee->id)->latest()->paginate(12);

        $counts = [
            'resume'  => Document::mine($employee->id)->where('doc_type','resume')->count(),
            'medical' => Document::mine($employee->id)->where('doc_type','medical')->count(),
            'mdr'     => Document::mine($employee->id)->whereIn('doc_type',['mdr_philhealth','mdr_sss','mdr_pagibig'])->count(),
            'other'   => Document::mine($employee->id)->where('doc_type','other')->count(),
        ];

        return view('my-documents.index', compact('docs','counts','employee'));
    }

    public function create()
    {
        abort_unless(auth()->user()->employee, 403);
        return view('my-documents.create');
    }

    /** Keep employee table mirror fields in sync with document uploads */
    private function mirrorToEmployeeFields($employee, string $docType, string $storedPath): void
    {
        if ($docType === 'resume') {
            $employee->update(['resume_file' => $storedPath]);
            return;
        }

        if (in_array($docType, ['mdr_philhealth','mdr_sss','mdr_pagibig'], true)) {
            $map = [
                'mdr_philhealth' => 'mdr_philhealth_file',
                'mdr_sss'        => 'mdr_sss_file',
                'mdr_pagibig'    => 'mdr_pagibig_file',
            ];
            $employee->update([$map[$docType] => $storedPath]);
            return;
        }

        if ($docType === 'medical') {
            $docs = $employee->medical_documents ?? [];
            $docs[] = $storedPath;
            $employee->update(['medical_documents' => array_values(array_unique($docs))]);
        }
    }

    public function store(DocumentStoreRequest $request)
    {
        $employee = auth()->user()->employee;
        abort_unless($employee, 403);

        $latest = Document::mine($employee->id)
            ->where('doc_type', $request->doc_type)
            ->where('title', $request->title)
            ->orderByDesc('version')
            ->first();
        $version = $latest ? $latest->version + 1 : 1;

        $path = $request->file('file')->store("documents/{$employee->id}", 'public');

        // Create the document row
        $doc = Document::create([
            'employee_id' => $employee->id,
            'uploaded_by' => auth()->id(),
            'title'       => $request->title,
            'doc_type'    => $request->doc_type,
            'file_path'   => $path,
            'version'     => $version,
            'notes'       => $request->notes,
            'visibility'  => $request->visibility ?? 'employee',
            'expires_at'  => $request->expires_at,
        ]);

        // Mirror to employee columns for quick HR access
        $this->mirrorToEmployeeFields($employee, $doc->doc_type, $path);

        return redirect()->route('mydocs.index')->with('success', 'Document uploaded.');
    }

    public function show(Document $document)
    {
        Gate::authorize('view', $document);
        return view('my-documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        Gate::authorize('update', $document);
        return view('my-documents.edit', compact('document'));
    }

    public function update(DocumentUpdateRequest $request, Document $document)
    {
        Gate::authorize('update', $document);

        $data = $request->validated();

        if ($request->hasFile('file')) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $data['file_path'] = $request->file('file')->store("documents/{$document->employee_id}", 'public');
            $data['version']   = $document->version + 1;

            // Keep mirror up to date
            $this->mirrorToEmployeeFields($document->employee, $request->doc_type, $data['file_path']);
        }

        $document->update($data);

        return redirect()->route('mydocs.show', $document)->with('success', 'Document updated.');
    }

    public function destroy(Document $document)
    {
        Gate::authorize('delete', $document);
        $document->delete();
        return redirect()->route('mydocs.index')->with('success', 'Document removed.');
    }

    public function download(Document $document)
    {
        Gate::authorize('view', $document);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($document->file_path), 404);

        $ext = pathinfo($document->file_path, PATHINFO_EXTENSION) ?: 'file';
        return $disk->download($document->file_path, "{$document->title}.{$ext}");
    }
}
