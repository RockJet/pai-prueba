<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;


class BookController extends Controller
{
    public function index(Request $request): JsonResponse {

        $validated = $request->validate([
            'sortBy' => 'sometimes|in:title,author,published_year,available_copies',
            'sortDirection' => 'sometimes|in:asc,desc',
        ]);

        $sortBy = $validated['sortBy'] ?? 'title';
        $sortDirection = $validated['sortDirection'] ?? 'asc';

        $query = Book::query();

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
        }

        $query->orderBy($sortBy, $sortDirection);

        $books = $query->paginate(10);

        return response()->json($books);

    }

    public function store(Request $request): JsonResponse {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn|max:20',
            'published_year' => 'required|integer|digits:4|max:' . date('Y'),
            'total_copies' => 'required|integer|min:1',
            'available_copies' => 'nullable|integer|min:0|lte:total_copies',
        ]);

        if (!isset($validated['available_copies']) && isset($validated['total_copies'])) {
            $validated['available_copies'] = $validated['total_copies'];
        }

        $book = Book::create($validated);

        return response()->json([
            'message' => 'Book created successfully.',
            'book' => $book,
        ], 201);
    }

    public function show(int $id): JsonResponse {
        $book = Book::findOrFail($id);

        return response()->json($book);
    }

    public function update(Request $request, int $id): JsonResponse {
        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'isbn' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('books', 'isbn')->ignore($book->id),
            ],
            'published_year' => 'sometimes|required|integer|digits:4|max:' . date('Y'),
            'total_copies' => 'sometimes|required|integer|min:1',
            'available_copies' => 'sometimes|nullable|integer|min:0|lte:total_copies',
        ]);

        $book->update($validated);

        return response()->json([
            'message' => 'Book updated successfully.',
            'book' => $book,
        ]);
    }

    public function destroy(int $id): JsonResponse {
        $book = Book::findOrFail($id);

        if ($book->loans()->whereNull('returned_at')->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el libro. Tiene préstamos pendientes por devolver.',
            ], 409);
        }

        $book->delete();

        return response()->json(['message' => 'Se ha eliminado el libro correctamente'], 200);
    }
}
