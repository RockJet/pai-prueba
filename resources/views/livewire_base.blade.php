<!-- resources/views/livewire_base.html -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library App - Livewire</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Optional: Use Inter font for modern look */
        body { font-family: 'Inter', sans-serif; }
    </style>

    <!-- MANDATORY: Livewire Styles -->
    @livewireStyles
</head>
<body class="bg-gray-100 antialiased">

    <!-- The content of your pages will be rendered here -->
    <div class="container mx-auto p-4 sm:p-8">
        @yield('content')

        <!-- Example of embedding a Livewire component directly -->
        <livewire:book-list />
    </div>

    <!-- MANDATORY: Livewire Scripts -->
    @livewireScripts
</body>
</html>
