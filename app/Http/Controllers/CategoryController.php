<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->latest()->paginate(10);

        // Determine correct routes based on current user's role
        $role = strtolower(auth()->user()->role->name ?? 'admin');
        if ($role === 'warehouse') {
            $storeRoute  = 'warehouse.categories.store';
            $deleteRoute = 'warehouse.categories.delete';
            $updateUrlBase = '/warehouse/categories/';
        } else {
            $storeRoute  = 'admin.categories.store';
            $deleteRoute = 'admin.categories.delete';
            $updateUrlBase = '/admin/categories/';
        }

        return view('admin.categories.index', compact('categories', 'storeRoute', 'deleteRoute', 'updateUrlBase'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
        ]);

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->products()->count() > 0) {
            return redirect()->route('admin.categories')->with('error', 'Cannot delete category with products!');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully!');
    }
}
