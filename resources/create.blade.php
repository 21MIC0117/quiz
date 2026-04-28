@extends('layouts.app', ['title' => 'Add question'])

@section('content')
    <h1>Add a {{ $type->label() }} question</h1>
    <p class="muted">to <a href="{{ route('quizzes.show', $quiz) }}">{{ $quiz->title }}</a></p>

    <form method="POST" action="{{ route('questions.store', $quiz) }}" enctype="multipart/form-data" class="card">
        @csrf
        <input type="hidden" name="type" value="{{ $type->key() }}">

        <div class="field">
            <label for="prompt">Prompt</label>
            <textarea id="prompt" name="prompt" rows="3" required>{{ old('prompt') }}</textarea>
            <small class="muted">HTML & line breaks allowed.</small>
        </div>

        <div class="field-grid">
            <div class="field">
                <label for="marks">Marks</label>
                <input type="number" id="marks" name="marks" step="any" min="0" value="{{ old('marks', 1) }}">
            </div>
            <div class="field">
                <label for="video_url">Video URL (optional)</label>
                <input type="url" id="video_url" name="video_url" placeholder="https://youtu.be/..." value="{{ old('video_url') }}">
            </div>
        </div>

        <div class="field">
            <label for="question_image">Image (optional)</label>
            <input type="file" id="question_image" name="question_image" accept="image/*">
        </div>

        {!! $type->renderEditorFields() !!}

        <div class="actions">
            <button type="submit">Save question</button>
            <a href="{{ route('quizzes.show', $quiz) }}" class="btn-link">Cancel</a>
        </div>
    </form>
@endsection
