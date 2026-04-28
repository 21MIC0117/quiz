<?php

namespace App\QuestionTypes;

use App\Models\Question;
use Illuminate\Http\Request;

class MultipleChoiceType extends SingleChoiceType
{
    public function key(): string { return 'multiple_choice'; }
    public function label(): string { return 'Multiple Choice'; }

    public function renderEditorFields(): string
    {
        return $this->optionsEditorMarkup('multiple');
    }

    public function buildFromRequest(Request $request): array
    {
        $labels  = $request->input('option_label', []);
        $images  = $request->file('option_image', []);
        $correct = array_map('intval', (array) $request->input('option_correct', []));

        return [
            'config'  => null,
            'options' => $this->collectOptions($labels, $images, fn($i) => in_array($i, $correct, true)),
        ];
    }

    public function renderAttemptInput(Question $question, string $inputName): string
    {
        return self::choiceMarkup($question, $inputName, multiple: true);
    }

    public function evaluate(Question $question, mixed $response): float
    {
        if (!is_array($response) || empty($response)) return 0;
        $picked  = collect($response)->map(fn($v) => (int) $v)->sort()->values()->all();
        $correct = $question->options->where('is_correct', true)->pluck('id')->sort()->values()->all();

        return $picked === $correct ? (float) $question->marks : 0.0;
    }
}
