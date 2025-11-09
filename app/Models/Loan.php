<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Loan extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'loans';
    protected $name = 'LOAN';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'book_id',
        'loaned_at',
        'due_date',
        'returned_at'
    ];

    protected $casts = [
        'loaned_at' => 'datetime',
        'due_date' => 'date',
        'returned_at' => 'datetime',
    ];
    protected function loanDate(): Attribute {
        return Attribute::make(
            set: fn (string $value) => [
                'loan_date' => $value,
                // Automatically calculate and set the due_date when loan_date is set
                'due_date'  => Carbon::parse($value)->addDays(14),
            ],
        );
    }

    protected $attributes = [
        // Automatically set the loan date upon creation
        'loaned_at' => null, // Will be set in the controller using now()
    ];

    /**
     * Get the book associated with the loan.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
