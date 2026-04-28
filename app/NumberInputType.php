<?php

namespace App\QuestionTypes;

use App\Models\Question;
use Illuminate\Http\Request;

class NumberInputType extends AbstractQuestionType
{
    public function key(): string { return 'number_input'; }
    public function label(): string { return 'Number Input'; }

    public function renderEditorFields(): string
    {
        return <<<HTML
        <div class="field">
            <label>Correct number</label>
            <input type="number" name="number_correct" step="any" required>
        </div>
        <div class="field">
            <label>Tolerance (± allowed difference)</label>
            <input type="number" name="number_tolerance" step="any" value="0">
        </div>
        HTML;
    }

    public function buildFromRequest(Request $request): array
    {
        return [
            'config' => [
                'correct'   => (float) $request->input('number_correct'),
                'tolerance' => (float) $request->input('number_tolerance', 0),
            ],
            'options' => [],
        ];
    }

    public function renderAttemptInput(Question $question, string $inputName): string
    {
        return sprintf(
            '<input type="number" name="%s" step="any" required class="text-input">',
            e($inputName)
        );
    }

    public function evaluate(Question $question, mixed $response): float
    {
        if ($response === null || $response === '') return 0;
        $config    = $question->config ?? [];
        $correct   = (float) ($config['correct'] ?? 0);
        $tolerance = (float) ($config['tolerance'] ?? 0);
        return abs(((float) $response) - $correct) <= $tolerance
            ? (float) $question->marks
            : 0.0;
    }

    public function renderResponseSummary(Question $question, mixed $response): string
    {
        if ($response === null || $response === '') {
            return '<em class="muted">No response</em>';
        }
        $config  = $question->config ?? [];
        $correct = (float) ($config['correct'] ?? 0);
        $tol     = (float) ($config['tolerance'] ?? 0);
        $isOk    = abs(((float) $response) - $correct) <= $tol;
        $cls     = $isOk ? 'correct' : 'incorrect';
        return '<span class="pill ' . $cls . '">' . e((string) $response) . '</span>'
            . ' <span class="muted">(expected ' . e((string) $correct)
            . ($tol > 0 ? ' ± ' . e((string) $tol) : '') . ')</span>';
    }
}
