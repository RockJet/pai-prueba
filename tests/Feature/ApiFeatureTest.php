<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Subscription;
use PHPUnit\Framework\Attributes\Test;

class ApiFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Un usuario no autenticado no puede acceder a rutas protegidas.
     */
    #[Test]
    public function unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/books');

        $response->assertUnauthorized();
    }

    /**
     * Test 2: Un usuario con plan básico puede prestar un libro si hay copias y está dentro de su límite.
     */
    #[Test]
    public function basic_user_can_loan_a_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 5]);

        $response = $this->actingAs($user)->postJson('/api/loans', [
            'book_id' => $book->id,
        ]);

        // Assert: Verificamos que todo haya salido bien.
        $response->assertCreated(); // HTTP 201
        $this->assertDatabaseHas('loans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        $this->assertEquals(4, $book->fresh()->available_copies); // Verificamos que las copias disminuyeron.
    }

    /**
     * Test 3: Un usuario no puede prestar un libro si no quedan copias disponibles.
     * Este test verifica una regla de negocio crítica.
     */
    #[Test]
    public function user_cannot_loan_a_book_with_no_available_copies(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 0]);

        // Act: Intentamos prestar el libro.
        $response = $this->actingAs($user)->postJson('/api/loans', [
            'book_id' => $book->id,
        ]);

        $response->assertStatus(409);
        $this->assertDatabaseMissing('loans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * Test 4: Un usuario con plan básico no puede prestar más de 2 libros.
     * Este test verifica la lógica del límite de préstamos del LoanService.
     */
    #[Test]
    public function basic_user_cannot_exceed_loan_limit(): void
    {
        $user = User::factory()->create();
        Loan::factory()->count(2)->create(['user_id' => $user->id]);
        $bookToLoan = Book::factory()->create();

        // Act: Intentamos prestar un tercer libro.
        $response = $this->actingAs($user)->postJson('/api/loans', [
            'book_id' => $bookToLoan->id,
        ]);

        $response->assertStatus(409);
        $this->assertDatabaseCount('loans', 2);
    }

    /**
     * Test 5: Un usuario puede devolver un libro que tiene prestado.
     * Este test verifica la funcionalidad de devolución.
     */
    #[Test]
    public function user_can_return_a_loaned_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 9]);
        $loan = Loan::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user)->putJson("/api/loans/{$loan->id}/return");

        $response->assertOk(); // HTTP 200
        $this->assertNotNull($loan->fresh()->returned_at);
        $this->assertEquals(10, $book->fresh()->available_copies);
    }

    /**
     * Test 6: Un usuario puede mejorar su suscripción a premium.
     * Este test verifica el endpoint de suscripciones.
     */
    #[Test]
    public function user_can_subscribe_to_premium_plan(): void
    {
        $user = User::factory()->create();
        $factory = Subscription::factory()->create([
            'user_id' => $user->id
        ]);
        $response = $this->actingAs($user)->postJson('/api/subscriptions/subscribe', [
            'payment_token' => 'tok_simulado_valido',
        ]);

        $response->assertAccepted();
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_name' => 'premium',
            'status' => 'active',
        ]);
    }
}
