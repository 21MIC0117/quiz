<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\QuestionTypes\QuestionTypeRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function __construct(protected QuestionTypeRegistry $registry) {}

    public function create(Request $request, Quiz $quiz)
    {
        $type    = $this->registry->get($request->query('type', 'binary'));
        $allTypes = $this->registry->all();
        return view('questions.create', compact('quiz', 'type', 'allTypes'));
    }

    public function store(Request $request, Quiz $quiz)
    {
        $request->validate([
            'type'   => 'required|string',
            'prompt' => 'required|string',
            'marks'  => 'nullable|numeric|min:0',
        ]);

        $type    = $this->registry->get($request->input('type'));
        $payload = $type->buildFromRequest($request);

        DB::transaction(function () use ($request, $quiz, $type, $payload) {
            $imagePath = null;
            if ($request->hasFile('question_image') && $request->file('question_image')->isValid()) {
                $imagePath = $request->file('question_image')->store('uploads', 'public');
            }

            $position = ((int) $quiz->questions()->max('position')) + 1;

            $question = $quiz->questions()->create([
                'type'       => $type->key(),
                'prompt'     => $request->input('prompt'),
                'marks'      => (float) $request->input('marks', 1),
                'image_path' => $imagePath,
                'video_url'  => $request->input('video_url') ?: null,
                'config'     => $payload['config'],
                'position'   => $position,
            ]);

            foreach ($payload['options'] as $option) {
                $question->options()->create($option);
            }
        });

        return redirect()->route('quizzes.show', $quiz);
    }

    public function destroy(Quiz $quiz, Question $question)
    {
        abort_unless($question->quiz_id === $quiz->id, 404);
        $question->delete();
        return redirect()->route('quizzes.show', $quiz);
    }
}
