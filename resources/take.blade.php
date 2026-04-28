@extends('layouts.app', ['title' => $quiz->title])

@section('content')
    <h1>{{ $quiz->title }}</h1>
    @if ($quiz->description)<p class="muted">{{ $quiz->description }}</p>@endif

    <form method="POST" action="{{ route('attempts.submit', $quiz) }}" class="card">
        @csrf
        <div class="field">
            <label for="user_name">Your name (optional)</label>
            <input type="text" id="user_name" name="user_name">
        </div>

        <ol class="questions">
            @foreach ($quiz->questions as $q)
                <li class="card">
                    <div class="question-header">
                        <span class="badge">{{ $registry->get($q->type)->label() }}</span>
                        <span class="muted">· {{ $q->marks }} mark{{ $q->marks == 1 ? '' : 's' }}</span>
                    </div>
                    <div class="prompt">{!! nl2br(e($q->prompt)) !!}</div>
                    @if ($q->image_path)
                        <img class="q-img" src="{{ asset('storage/' . $q->image_path) }}" alt="">
                    @endif
                    @if ($q->video_url)
                        <p><a href="{{ $q->video_url }}" target="_blank" rel="noopener">Video</a></p>
                    @endif
                    <div class="answer-area">
                        {!! $registry->get($q->type)->renderAttemptInput($q, 'answer_' . $q->id) !!}
                    </div>
                </li>
            @endforeach
        </ol>

        <div class="actions">
            <button type="submit" class="btn-primary">Submit</button>
            <a href="{{ route('quizzes.show', $quiz) }}" class="btn-link">Cancel</a>
        </div>
    </form>
@endsection
