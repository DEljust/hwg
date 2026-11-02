<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::withCount('books')->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories',
        ]);

        return response()->json(Category::create($data), 201);
    }

    public function show(Category $category)
    {
        return $category->load('books');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,'.$category->id,
        ]);

        $category->update($data);

        return $category;
    }

    public function destroy(Category $category)
    {
        if ($category->books()->exists()) {
            return response()->json(['message' => 'Kategori masih dipakai oleh buku'], 422);
        }

        $category->delete();

        return response()->noContent();
    }
}
