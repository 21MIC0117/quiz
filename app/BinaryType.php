<?php

namespace App\QuestionTypes;

use App\Models\Question;
use Illuminate\Http\Request;

class BinaryType extends AbstractQuestionType
{
    public function key(): string { return 'binary'; }
    public function label(): string { return 'Binary (True / False)'; }
    public function usesOptions(): bool { return true; }

    public function renderEditorFields(): string
    {
        return <<<HTML
        <div class="field">
            <label>Correct answer</label>
            <select name="binary_correct" required>
                <option value="true">True</option>
                <option value="false">False</option>
            </select>
        </div>
        HTML;
    }

    public function buildFromRequest(Request $request): array
    {
        $correct = $request->input('binary_correct') === 'true';

        return [
            'config'  => null,
            'options' => [
                ['label' => 'True',  'is_correct' => $correct,  'position' => 0],
                ['label' => 'False', 'is_correct' => !$correct, 'position' => 1],
            ],
        ];
    }

    public function renderAttemptInput(Question $question, string $inputName): string
    {
        $html = '';
        foreach ($question->options as $option) {
            $html .= sprintf(
                '<label class="option"><input type="radio" name="%s" value="%d" required> %s</label>',
                e($inputName),
                $option->id,
                $this->e($option->label)
            );
        }
        return $html;
    }

    public function evaluate(Question $question, mixed $response): float
    {
        if (!$response) return 0;
        $option = $question->options->firstWhere('id', (int) $response);
        return $option && $option->is_correct ? (float) $question->marks : 0.0;
    }

    public function renderResponseSummary(Question $question, mixed $response): string
    {
        $option = $question->options->firstWhere('id', (int) $response);
        if (!$option) {
            return '<em class="muted">No response</em>';
        }
        $cls = $option->is_correct ? 'correct' : 'incorrect';
        return '<span class="pill ' . $cls . '">' . $this->e($option->label) . '</span>';
    }
}
