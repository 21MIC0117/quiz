@extends('layouts.app', ['title' => 'Result'])

@section('content')
    <h1>Result</h1>
    <p class="muted"><a href="{{ route('quizzes.show', $attempt->quiz) }}">{{ $attempt->quiz->title }}</a></p>

    <div class="score-banner">
        <div class="score-num">{{ rtrim(rtrim(number_format($attempt->total_score, 2), '0'), '.') }} / {{ rtrim(rtrim(number_format($attempt->max_score, 2), '0'), '.') }}</div>
        <div class="score-pct">{{ $attempt->percentage }}%</div>
        @if ($attempt->user_name)
            <div class="muted">{{ $attempt->user_name }}</div>
        @endif
    </div>

    <ol class="questions">
        @foreach ($attempt->answers as $a)
            @php $q = $a->question; @endphp
            <li class="card">
                <div class="question-header">
                    <span class="badge">{{ $registry->get($q->type)->label() }}</span>
                    <span class="muted">· {{ rtrim(rtrim(number_format($a->awarded_marks, 2), '0'), '.') }} / {{ $q->marks }}</span>
                </div>
                <div class="prompt">{!! nl2br(e($q->prompt)) !!}</div>
                <div class="answer-area">
                    {!! $registry->get($q->type)->renderResponseSummary($q, $a->response) !!}
                </div>
            </li>
        @endforeach
    </ol>

    <div class="actions">
        <a href="{{ route('quizzes.show', $attempt->quiz) }}" class="btn-secondary">Back to quiz</a>
        <a href="{{ route('attempts.take', $attempt->quiz) }}" class="btn-primary">Try again</a>
    </div>
@endsection
