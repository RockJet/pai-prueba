<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use App\Events\BookLoaned;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LoanService
{
    const MAX_BASIC_LOANS = 2;
    const MAX_PREMIUM_LOANS = 5;

    public function index(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Loan::query()
            ->where('user_id', $user->id)
            ->with('book')
            ->latest('loaned_at');

        if (isset($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->whereNull('returned_at');
            } elseif ($filters['status'] === 'returned') {
                $query->whereNotNull('returned_at');
            }
        }

        return $query->paginate($perPage);
    }

    public function createLoan(User $user, int $bookId): Loan
    {
        $planName = $user->subscription->plan_name ?? 'basic';
        $loanLimit = ($planName === 'premium') ? self::MAX_PREMIUM_LOANS : self::MAX_BASIC_LOANS;

        $activeLoansCount = Loan::where('user_id', $user->id)
            ->whereNull('returned_at')
            ->count();

        if ($activeLoansCount >= $loanLimit) {
            throw new \Exception("Has alcanzado el límite de {$loanLimit} préstamos activos.");
        }

        return DB::transaction(function () use ($bookId, $user) {
            $book = Book::lockForUpdate()->findOrFail($bookId);


            if ($book->available_copies <= 0) {
                throw new \Exception("Lo sentimos, no quedan copias de '{$book->title}' disponibles en este momento.");
            }

            $book->decrement('available_copies');

            $loan = Loan::create([
                'user_id' => $user->id,
                'book_id' => $bookId,
                'loaned_at' => now(),
                'due_date' => now()->addDays(14),
            ]);

            BookLoaned::dispatch($loan);

            return $loan;
        });
    }

        public function returnLoan(Loan $loan, User $user): Loan
    {
        if ($loan->user_id !== $user->id) {
            throw new \Exception('This loan does not belong to you.');
        }

        if ($loan->returned_at !== null) {
            throw new \Exception('This book has already been returned.');
        }

        return DB::transaction(function () use ($loan) {
            $loan->update(['returned_at' => now()]);

            $loan->book->increment('available_copies');

            return $loan;
        });
    }
}
