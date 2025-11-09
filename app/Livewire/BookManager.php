<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Book;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;

#[Layout('layouts.app')] // Asume que tienes un layout 'app.blade.php'
#[Title('Gestión de Libros')]
class BookManager extends Component
{
    use WithPagination;

    // Propiedad para la búsqueda reactiva
    public string $search = '';

    // Propiedades para el modal de crear/editar
    public bool $modalIsOpen = false;
    public ?int $bookId = null;

    // Propiedades del formulario (con validación de atributos)
    #[Rule('required|string|max:255')]
    public string $title = '';

    #[Rule('required|string|max:255')]
    public string $author = '';

    #[Rule('required|string|unique:books,isbn')]
    public string $isbn = '';

    #[Rule('required|integer|min:1900')]
    public int $published_year = 2024;

    #[Rule('required|integer|min:0')]
    public int $total_copies = 1;

    /**
     * Resetea la paginación cada vez que la propiedad 'search' cambia.
     * Livewire 3 lo hace más fácil con el atributo #[Url]
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Renderiza el componente.
     */
    public function render()
    {
        $books = Book::where('title', 'like', '%' . $this->search . '%')
            ->orWhere('author', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.book-manager', [
            'books' => $books,
        ]);
    }

    /**
     * Abre el modal para crear un nuevo libro.
     */
    public function create()
    {
        $this->resetForm();
        $this->modalIsOpen = true;
    }

    /**
     * Abre el modal para editar un libro existente.
     */
    public function edit(Book $book)
    {
        $this->bookId = $book->id;
        $this->title = $book->title;
        $this->author = $book->author;
        $this->isbn = $book->isbn;
        $this->published_year = $book->published_year;
        $this->total_copies = $book->total_copies;

        // Ajustamos la regla de 'isbn' para que ignore el registro actual
        $this->rules['isbn'] = 'required|string|unique:books,isbn,' . $this->bookId;

        $this->modalIsOpen = true;
    }

    /**
     * Guarda el libro (nuevo o existente).
     */
    public function save()
    {
        // Si estamos editando, ajustamos la regla de ISBN
        if ($this->bookId) {
            $this->rules['isbn'] = 'required|string|unique:books,isbn,' . $this->bookId;
        }

        $this->validate();

        // Creamos o actualizamos el libro
        Book::updateOrCreate(
            ['id' => $this->bookId],
            [
                'title' => $this->title,
                'author' => $this->author,
                'isbn' => $this->isbn,
                'published_year' => $this->published_year,
                'total_copies' => $this->total_copies,
                // Al crear, 'available_copies' se setea igual a 'total_copies'
                // Podríamos manejar esto en el 'creating' event del modelo Book
                'available_copies' => $this->bookId ? Book::find($this->bookId)->available_copies : $this->total_copies,
            ]
        );

        session()->flash('success', $this->bookId ? 'Libro actualizado.' : 'Libro creado.');
        $this->closeModal();
    }

    /**
     * Elimina (Soft Delete) un libro.
     */
    public function delete(Book $book)
    {
        $book->delete();
        session()->flash('success', 'Libro eliminado (soft delete).');
    }

    /**
     * Cierra el modal.
     */
    public function closeModal()
    {
        $this->modalIsOpen = false;
        $this->resetForm();
    }

    /**
     * Resetea las propiedades del formulario.
     */
    private function resetForm()
    {
        $this->reset(['bookId', 'title', 'author', 'isbn', 'published_year', 'total_copies']);
        $this->rules['isbn'] = 'required|string|unique:books,isbn'; // Resetea la regla
        $this->resetErrorBag();
    }

    /**
     * Define la vista de paginación (para Bootstrap)
     */
    public function paginationView()
    {
        return 'pagination::bootstrap-5';
    }
}
