<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        return Book::with('category')
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->search, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
        ]);

        return response()->json(Book::create($data)->load('category'), 201);
    }

    public function show(Book $book)
    {
        return $book->load('category');
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'title' => 'sometimes|string|max:255',
            'author' => 'sometimes|string|max:255',
            'stock' => 'sometimes|integer|min:0',
        ]);

        $book->update($data);

        return $book->load('category');
    }

    public function destroy(Book $book)
    {
        if ($book->loans()->whereNull('returned_at')->exists()) {
            return response()->json(['message' => 'Buku masih dipinjam'], 422);
        }

        $book->delete();

        return response()->noContent();
    }
}
