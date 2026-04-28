<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Quiz' }}</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>
<body>
    <header class="topbar">
        <div class="container">
            <a href="{{ route('home') }}" class="brand">Quiz</a>
            <nav>
                <a href="{{ route('quizzes.create') }}">+ New Quiz</a>
            </nav>
        </div>
    </header>

    <main class="container">
        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
