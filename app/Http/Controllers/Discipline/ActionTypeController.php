<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Discipline\ActionType;

class ActionTypeController extends Controller
{
    public function index()
    {
        $types = ActionType::paginate(10);
        return view('discipline::types.index', compact('types'));
    }

    public function create()
    {
        return view('discipline::types.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'code'=>'required|unique:action_types,code',
            'description'=>'required|string',
            'severity_level'=>'required|string',
            'outcome'=>'required|string',
            'suspension_policy'=>'nullable|string',
            'leave_days'=>'nullable|integer',
            'status'=>'required|in:Active,Inactive',
        ]);
        ActionType::create($data);
        return redirect()->route('discipline.types.index')->with('success','Type added.');
    }

    public function edit(ActionType $type)
    {
        return view('discipline::types.edit', compact('type'));
    }

    public function update(Request $r, ActionType $type)
    {
        $data = $r->validate([
            'code'=>"required|unique:action_types,code,{$type->id}",
            'description'=>'required|string',
            'severity_level'=>'required|string',
            'outcome'=>'required|string',
            'suspension_policy'=>'nullable|string',
            'leave_days'=>'nullable|integer',
            'status'=>'required|in:Active,Inactive',
        ]);
        $type->update($data);
        return redirect()->route('discipline.types.index')->with('success','Type updated.');
    }

    public function destroy(ActionType $type)
    {
        $type->delete();
        return back()->with('success','Removed.');
    }
}
