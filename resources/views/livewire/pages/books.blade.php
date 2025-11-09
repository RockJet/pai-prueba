<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Services\LoanService;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

new #[Layout('layouts.app')]
#[Title('Catálogo de Libros')]
class extends Component
{
    use WithPagination;

    public array $userLoanMap = [];
    public string $message = '';
    public string $messageType = '';

    #[Url]
    public string $sortBy = 'title';
    #[Url]
    public string $sortDirection = 'asc';

    #[Url(as: 'filtro')]
    public string $filter = 'all';

    public function mount(): void
    {
        $this->loadUserLoans();    }

    public function setSort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function loadUserLoans(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $filters = ['status' => 'active'];

        // Usamos app() para obtener una instancia del servicio dentro del componente
        $loanService = app(LoanService::class);

        // Obtenemos todos los préstamos activos del usuario (sin paginación para el mapa)
        // Pedimos un número alto de resultados para asegurarnos de traerlos todos.
        $activeLoans = $loanService->index($user, $filters, 999);

        // Creamos el mapa que la vista necesita a partir de la colección de préstamos
        $this->userLoanMap = $activeLoans->pluck('id', 'book_id')->all();
    }

    public function setFilter(string $newFilter): void
    {
        $this->filter = $newFilter;
        $this->resetPage();
    }

    public function loanBook(int $bookId, LoanService $loanService): void
    {
        $this->message = '';
        $user = Auth::user();

        try {
            $loanService->createLoan($user, $bookId);
            $bookTitle = Book::find($bookId)->title;
            $this->message = "¡Préstamo exitoso! Has tomado el libro: '{$bookTitle}'.";
            $this->messageType = 'success';
            $this->loadUserLoans();

            Cache::flush();

        } catch (\Exception $e) {
            Log::error("Fallo al realizar el préstamo: " . $e->getMessage());
            $this->message = "Error: " . $e->getMessage();
            $this->messageType = 'error';
        }
    }


    public function returnBook(int $loanId, LoanService $loanService): void
    {
        $this->message = '';
        $user = Auth::user();
        $loan = Loan::findOrFail($loanId);

        try {
            $loanService->returnLoan($loan, $user);

            $this->message = "¡Libro '{$loan->book->title}' devuelto exitosamente!";
            $this->messageType = 'success';


            $this->loadUserLoans();

            Cache::flush();

        } catch (\Exception $e) {
            Log::error("Fallo al devolver el libro desde Livewire: " . $e->getMessage());
            $this->message = "Error: " . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function render(): View
    {
        // Creamos una clave única para la caché que depende de todos los parámetros
        // que pueden cambiar el resultado: página, orden, dirección y filtro.
        $cacheKey = 'books-page-' . $this->getPage() . "-sort-{$this->sortBy}-{$this->sortDirection}-filter-{$this->filter}";

        // Usamos Cache::remember para obtener los datos
        $paginatedBooks = Cache::remember($cacheKey, now()->addMinutes(15), function () use($cacheKey) {

            Log::info("CACHE MISS: Generando lista de libros para la clave: {$cacheKey}"); // Log para depurar

            $query = Book::query();

            if ($this->filter === 'loaned') {
                $loanedBookIds = array_keys($this->userLoanMap);
                $query->whereIn('id', count($loanedBookIds) > 0 ? $loanedBookIds : [0]);
            }

            return $query->orderBy($this->sortBy, $this->sortDirection)
                         ->paginate(10);
        });

        return view('livewire.pages.books', [
            'books' => $paginatedBooks,
        ]);
    }
};
?>

<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($message)
                        <div class="mb-4 p-4 rounded-lg border
                            {{ $messageType === 'success' ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-700 border-red-300' }}"
                            role="alert">
                            {{ $message }}
                        </div>
                    @endif

                    {{-- Tabla de Libros --}}
                    <div class="overflow-x-auto shadow-md sm:rounded-lg">
                        <div class="flex items-center space-x-2 mb-4">
                            <span class="text-sm font-medium text-gray-700">Mostrar:</span>

                            <button wire:click="setFilter('all')"
                                    class="px-4 py-2 text-sm font-semibold rounded-lg transition duration-150
                                        {{ $filter === 'all' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border' }}">
                                Todos
                            </button>

                            <button wire:click="setFilter('loaned')"
                                    class="px-4 py-2 text-sm font-semibold rounded-lg transition duration-150
                                        {{ $filter === 'loaned' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border' }}">
                                Mis Préstamos
                            </button>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    @php
                                        $columns = [
                                            'title' => 'Título',
                                            'author' => 'Autor',
                                            'published_year' => 'Año',
                                            'isbn' => 'ISBN',
                                            'available_copies' => 'Copias Disponibles',
                                        ];
                                    @endphp

                                    @foreach ($columns as $key => $label)
                                        <th wire:click="setSort('{{ $key }}')"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition duration-150 whitespace-nowrap">
                                            <div class="flex items-center">
                                                {{ __($label) }}
                                                @if ($sortBy === $key)
                                                    @if ($sortDirection === 'asc')
                                                        <svg class="w-4 h-4 ml-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                                    @else
                                                        <svg class="w-4 h-4 ml-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                    @endif
                                                @else
                                                    <svg class="w-4 h-4 ml-1 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                                                @endif
                                            </div>
                                        </th>
                                    @endforeach

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Acción') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($books as $book)
                                    <tr wire:key="book-{{ $book->id }}" class="hover:bg-indigo-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $book->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $book->author }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $book->published_year }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $book->isbn }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-lg font-bold {{ $book->available_copies > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $book->available_copies }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if(isset($userLoanMap[$book->id]))
                                                <button wire:click="returnBook({{ $userLoanMap[$book->id] }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="returnBook({{ $userLoanMap[$book->id] }})"
                                                        class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg text-xs transition duration-150 ease-in-out disabled:bg-gray-400 disabled:cursor-not-allowed shadow-md">
                                                    <span wire:loading.remove wire:target="returnBook({{ $userLoanMap[$book->id] }})">{{ __('Devolver') }}</span>
                                                    <span wire:loading wire:target="returnBook({{ $userLoanMap[$book->id] }})">{{ __('Procesando...') }}</span>
                                                </button>
                                            @else
                                                @if ($book->available_copies > 0)
                                                    <button wire:click="loanBook({{ $book->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="loanBook({{ $book->id }})"
                                                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-xs transition duration-150 ease-in-out disabled:bg-gray-400 disabled:cursor-not-allowed shadow-md">
                                                        <span wire:loading.remove wire:target="loanBook({{ $book->id }})">{{ __('Prestar Libro') }}</span>
                                                        <span wire:loading wire:target="loanBook({{ $book->id }})">{{ __('Procesando...') }}</span>
                                                    </button>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold leading-5 rounded-full bg-red-100 text-red-800">
                                                        {{ __('Agotado') }}
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            {{ __('No hay libros registrados en el catálogo.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $books->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
