<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\QuestionTypes\QuestionTypeRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function __construct(protected QuestionTypeRegistry $registry) {}

    public function index()
    {
        $quizzes = Quiz::withCount('questions')->latest()->get();
        return view('quizzes.index', compact('quizzes'));
    }

    public function create()
    {
        return view('quizzes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $quiz = Quiz::create($data);
        return redirect()->route('quizzes.show', $quiz);
    }

    public function show(Quiz $quiz)
    {
        $quiz->load('questions.options');
        $attempts = $quiz->attempts()->get();
        $types    = $this->registry->all();
        return view('quizzes.show', compact('quiz', 'attempts', 'types'));
    }

    public function destroy(Quiz $quiz)
    {
        DB::transaction(fn() => $quiz->delete());
        return redirect()->route('quizzes.index');
    }
}
