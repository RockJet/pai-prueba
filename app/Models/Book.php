<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'books';
    protected $name = 'BOOK';
    public $timestamps = true;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'published_year',
        'available_copies',
        'total_copies'
    ];

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
