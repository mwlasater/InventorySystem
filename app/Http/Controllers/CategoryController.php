<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::topLevel()->with('children')->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Category::topLevel()->get();
        return view('categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        if ($request->parent_id) {
            $parent = Category::find($request->parent_id);
            if ($parent && $parent->parent_id) {
                return back()->withErrors(['parent_id' => 'Only two-level hierarchy is supported.'])->withInput();
            }
        }

        $category = Category::create($request->only('name', 'parent_id', 'description'));
        AuditLog::record('create', 'categories', $category->id, null, $category->toArray());

        return redirect()->route('categories.index')->with('success', "Category '{$category->name}' created.");
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::topLevel()->where('id', '!=', $category->id)->get();
        return view('categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        if ($request->parent_id) {
            $parent = Category::find($request->parent_id);
            if ($parent && $parent->parent_id) {
                return back()->withErrors(['parent_id' => 'Only two-level hierarchy is supported.'])->withInput();
            }
            if ($request->parent_id == $category->id) {
                return back()->withErrors(['parent_id' => 'A category cannot be its own parent.'])->withInput();
            }
        }

        $old = $category->toArray();
        $category->update($request->only('name', 'parent_id', 'description'));
        AuditLog::record('update', 'categories', $category->id, $old, $category->toArray());

        return redirect()->route('categories.index')->with('success', "Category '{$category->name}' updated.");
    }

    public function destroy(Category $category)
    {
        if ($category->items()->exists()) {
            return back()->with('error', 'Cannot delete category with assigned items.');
        }

        $old = $category->toArray();
        $name = $category->name;
        $category->children()->update(['parent_id' => null]);
        $category->delete();
        AuditLog::record('delete', 'categories', $old['id'], $old, null);

        return redirect()->route('categories.index')->with('success', "Category '{$name}' deleted.");
    }
}
