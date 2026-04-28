<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Quiz;
use App\QuestionTypes\QuestionTypeRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttemptController extends Controller
{
    public function __construct(protected QuestionTypeRegistry $registry) {}

    public function take(Quiz $quiz)
    {
        abort_if($quiz->questions()->count() === 0, 404, 'This quiz has no questions yet.');
        $quiz->load('questions.options');
        $registry = $this->registry;
        return view('attempts.take', compact('quiz', 'registry'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $quiz->load('questions.options');

        $attempt = DB::transaction(function () use ($request, $quiz) {
            $totalScore = 0.0;
            $maxScore   = 0.0;
            $records    = [];

            foreach ($quiz->questions as $q) {
                $maxScore += (float) $q->marks;
                $type     = $this->registry->get($q->type);
                $response = $request->input('answer_' . $q->id);
                $awarded  = $type->evaluate($q, $response);
                $totalScore += $awarded;
                $records[] = [
                    'question_id'   => $q->id,
                    'response'      => $response,
                    'awarded_marks' => $awarded,
                ];
            }

            $attempt = $quiz->attempts()->create([
                'user_name'    => $request->input('user_name') ?: null,
                'total_score'  => $totalScore,
                'max_score'    => $maxScore,
                'started_at'   => now(),
                'completed_at' => now(),
            ]);

            foreach ($records as $r) {
                $attempt->answers()->create($r);
            }

            return $attempt;
        });

        return redirect()->route('attempts.show', $attempt);
    }

    public function show(Attempt $attempt)
    {
        $attempt->load(['quiz', 'answers.question.options']);
        $registry = $this->registry;
        return view('attempts.result', compact('attempt', 'registry'));
    }
}
