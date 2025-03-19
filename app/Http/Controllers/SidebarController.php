<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SidebarItem;

class SidebarController extends Controller
{
    public function index()
    {
        $items = SidebarItem::all();
        return view('sidebars.index', compact('items'));
    }

    public function create()
    {
        return view('sidebars.create');
    }

    public function store(Request $request)
    {
        SidebarItem::create($request->all());
        return redirect()->route('sidebars.index')->with('success', 'Sidebar Item Added');
    }

    public function edit($id)
    {
        $item = SidebarItem::findOrFail($id);
        return view('sidebars.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = SidebarItem::findOrFail($id);
        $item->update($request->all());
        return redirect()->route('sidebars.index')->with('success', 'Sidebar Item Updated');
    }

    public function destroy($id)
    {
        SidebarItem::destroy($id);
        return redirect()->route('sidebars.index')->with('success', 'Sidebar Item Deleted');
    }
}
