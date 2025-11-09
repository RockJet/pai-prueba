<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use App\Models\Loan;
use Carbon\Carbon;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = Book::factory(30)->create();

        $userIds = User::pluck('id')->all();

        for ($i = 0; $i < 20; $i++) {
            $randomUserId = $userIds[array_rand($userIds)];

            $bookToLoan = Book::where('available_copies', '>', 0)
                ->inRandomOrder()
                ->first();

            if (!$bookToLoan) {
                echo "No more books available for loaning. Stopping seeder loop.\n";
                break;
            }

            $randomBookId = $bookToLoan->id;

            $isReturned = rand(1, 10) <= 5;

            if (!$isReturned) {
                $activeLoansCount = Loan::where('user_id', $randomUserId)
                    ->whereNull('returned_at')
                    ->count();

                if ($activeLoansCount >= 2) {
                    // Si el usuario ya tiene 2 o más préstamos activos, saltamos esta iteración.
                    continue;
                }
                if(!Book::find($randomBookId)->pluck('available_copies')) {
                    continue;
                }

                $bookToLoan->decrement('available_copies');
            }

            $loanedAt = now()->subDays(rand(1, 60));

            Loan::create([
                'user_id' => $randomUserId,
                'book_id' => $randomBookId,
                'loaned_at' => $loanedAt,
                'due_date' => $loanedAt->addDays(14),
                'returned_at' => $isReturned
                    ? $loanedAt->copy()->addDays(rand(1, 14))
                    : null,
            ]);
        }
    }

}
