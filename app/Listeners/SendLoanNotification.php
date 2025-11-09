<?php

namespace App\Listeners;

use App\Events\BookLoaned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendLoanNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BookLoaned $event): void
    {
        $loan = $event->loan;
        $user = $loan->user;
        $book = $loan->book;

        Log::info("Confirmación de préstamo: Libro ID {$loan->book_id} ('{$book->title}') prestado al usuario '{$user->email}'.");
    }
}
