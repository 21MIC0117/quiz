<?php

namespace App\QuestionTypes;

use App\Models\Question;
use Illuminate\Http\Request;

class TextInputType extends AbstractQuestionType
{
    public function key(): string { return 'text_input'; }
    public function label(): string { return 'Text Input'; }

    public function renderEditorFields(): string
    {
        return <<<HTML
        <div class="field">
            <label>Correct text</label>
            <input type="text" name="text_correct" required>
        </div>
        <div class="field">
            <label><input type="checkbox" name="text_case_sensitive" value="1"> Case sensitive</label>
        </div>
        HTML;
    }

    public function buildFromRequest(Request $request): array
    {
        return [
            'config' => [
                'correct'        => (string) $request->input('text_correct', ''),
                'case_sensitive' => $request->boolean('text_case_sensitive'),
            ],
            'options' => [],
        ];
    }

    public function renderAttemptInput(Question $question, string $inputName): string
    {
        return sprintf(
            '<input type="text" name="%s" required class="text-input">',
            e($inputName)
        );
    }

    public function evaluate(Question $question, mixed $response): float
    {
        if (!is_string($response) || $response === '') return 0;
        $config  = $question->config ?? [];
        $correct = (string) ($config['correct'] ?? '');
        $cs      = (bool) ($config['case_sensitive'] ?? false);
        $match   = $cs ? $response === $correct : strcasecmp($response, $correct) === 0;
        return $match ? (float) $question->marks : 0.0;
    }

    public function renderResponseSummary(Question $question, mixed $response): string
    {
        if (!is_string($response) || $response === '') {
            return '<em class="muted">No response</em>';
        }
        $config  = $question->config ?? [];
        $correct = (string) ($config['correct'] ?? '');
        $cs      = (bool) ($config['case_sensitive'] ?? false);
        $isOk    = $cs ? $response === $correct : strcasecmp($response, $correct) === 0;
        $cls     = $isOk ? 'correct' : 'incorrect';
        return '<span class="pill ' . $cls . '">' . e($response) . '</span>'
            . ' <span class="muted">(expected ' . e($correct) . ')</span>';
    }
}
