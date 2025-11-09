<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Events\BookLoaned;
use Illuminate\Support\Facades\Log;
use App\Services\LoanService;

class LoanController extends Controller
{
    public function index(Request $request, LoanService $loanService): JsonResponse
    {
        $user = Auth::user();

        $filters = $request->only(['status']);

        $loans = $loanService->index($user);

        return response()->json($loans);
    }


    public function store(Request $request, LoanService $loanService): JsonResponse
    {
        $validated = $request->validate([
            'book_id' => 'required|integer|exists:books,id'
        ]);

        try {
            $loan = $loanService->createLoan(Auth::user(), $validated['book_id']);
            $loan->load('book');

            return response()->json([
                'message' => 'Libro prestado exitosamente.',
                'loan' => $loan,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Fallo al realizar el préstamo desde la API: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }


    public function returnBook(int $id, LoanService $loanService): JsonResponse
    {
        $user = Auth::user();

        try {
            $loan = Loan::findOrFail($id);

            $updatedLoan = $loanService->returnLoan($loan, $user);

            $updatedLoan->load('book');

            return response()->json([
                'message' => 'Libro devuelto exitosamente.',
                'loan' => $updatedLoan,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Préstamo no encontrado.'], 404);

        } catch (\Exception $e) {
            Log::error('Fallo al procesar la devolución desde la API: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }
}
