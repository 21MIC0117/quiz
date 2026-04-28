@extends('layouts.app', ['title' => 'Quizzes'])

@section('content')
    <h1>Quizzes</h1>

    @if ($quizzes->isEmpty())
        <p class="muted">No quizzes yet. <a href="{{ route('quizzes.create') }}">Create the first one</a>.</p>
    @else
        <table class="quizzes">
            <thead>
                <tr><th>Title</th><th>Questions</th><th>Created</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($quizzes as $quiz)
                    <tr>
                        <td><a href="{{ route('quizzes.show', $quiz) }}">{{ $quiz->title }}</a></td>
                        <td>{{ $quiz->questions_count }}</td>
                        <td class="muted">{{ $quiz->created_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <a class="btn-secondary" href="{{ route('quizzes.show', $quiz) }}">Manage</a>
                            @if ($quiz->questions_count > 0)
                                <a class="btn-secondary" href="{{ route('attempts.take', $quiz) }}">Take</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
