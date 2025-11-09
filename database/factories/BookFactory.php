<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
        // protected $model = Book::class;

    public function definition(): array
    {
        $copies = rand(1,5);
        return [
            'title' => fake()->unique()->words(rand(3, 8), true),
            'author' => fake()->name(),
            'published_year' => fake()->year(),
            'isbn' => fake()->unique()->isbn13(),
            'available_copies' => $copies,
            'total_copies' => $copies,
        ];
    }
}
