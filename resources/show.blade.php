@extends('layouts.app', ['title' => $quiz->title])

@section('content')
    <h1>{{ $quiz->title }}</h1>
    @if ($quiz->description)
        <p class="muted">{{ $quiz->description }}</p>
    @endif

    <div class="actions" style="margin-bottom: 24px;">
        @if ($quiz->questions->isNotEmpty())
            <a href="{{ route('attempts.take', $quiz) }}" class="btn-primary">Take quiz</a>
        @endif
        <form method="POST" action="{{ route('quizzes.destroy', $quiz) }}" style="display:inline"
              onsubmit="return confirm('Delete this quiz and all its data?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger">Delete quiz</button>
        </form>
    </div>

    <h2 style="font-size:18px">Questions</h2>

    @if ($quiz->questions->isEmpty())
        <p class="muted">No questions yet.</p>
    @else
        <ol class="questions">
            @foreach ($quiz->questions as $q)
                <li class="card">
                    <div class="question-header">
                        <span class="badge">{{ $types[$q->type]->label() }}</span>
                        <span class="muted">· {{ $q->marks }} mark{{ $q->marks == 1 ? '' : 's' }}</span>
                        <form method="POST" action="{{ route('questions.destroy', [$quiz, $q]) }}"
                              style="display:inline; float:right"
                              onsubmit="return confirm('Delete this question?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-link-danger">Delete</button>
                        </form>
                    </div>
                    <div class="prompt">{!! nl2br(e($q->prompt)) !!}</div>
                    @if ($q->image_path)
                        <img class="q-img" src="{{ asset('storage/' . $q->image_path) }}" alt="">
                    @endif
                    @if ($q->video_url)
                        <p><a href="{{ $q->video_url }}" target="_blank" rel="noopener">Video</a></p>
                    @endif
                    @if ($q->options->isNotEmpty())
                        <ul class="options-preview">
                            @foreach ($q->options as $opt)
                                <li class="{{ $opt->is_correct ? 'correct' : '' }}">
                                    @if ($opt->image_path)
                                        <img src="{{ asset('storage/' . $opt->image_path) }}" alt="" class="opt-img">
                                    @endif
                                    {{ $opt->label ?? '(image)' }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif

    <div class="add-question">
        <h3>Add a question</h3>
        <div class="type-buttons">
            @foreach ($types as $key => $t)
                <a class="btn-secondary" href="{{ route('questions.create', $quiz) }}?type={{ $key }}">+ {{ $t->label() }}</a>
            @endforeach
        </div>
    </div>

    <h2 style="font-size:18px;margin-top:32px">Scores</h2>
    @if ($attempts->isEmpty())
        <p class="muted">Nobody has taken this quiz yet.</p>
    @else
        <table class="scores">
            <thead>
                <tr><th>#</th><th>Name</th><th>Score</th><th>%</th><th>Submitted</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($attempts as $a)
                    <tr>
                        <td>{{ $a->id }}</td>
                        <td>{{ $a->user_name ?: '' }}@unless($a->user_name)<em class="muted">Anonymous</em>@endunless</td>
                        <td>{{ rtrim(rtrim(number_format($a->total_score, 2), '0'), '.') }} / {{ rtrim(rtrim(number_format($a->max_score, 2), '0'), '.') }}</td>
                        <td>{{ $a->percentage }}%</td>
                        <td class="muted">{{ $a->completed_at?->format('Y-m-d H:i:s') }}</td>
                        <td><a href="{{ route('attempts.show', $a) }}">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
