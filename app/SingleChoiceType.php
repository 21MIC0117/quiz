<?php

namespace App\QuestionTypes;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SingleChoiceType extends AbstractQuestionType
{
    public function key(): string { return 'single_choice'; }
    public function label(): string { return 'Single Choice'; }
    public function usesOptions(): bool { return true; }

    public function renderEditorFields(): string
    {
        return $this->optionsEditorMarkup('single');
    }

    public function buildFromRequest(Request $request): array
    {
        $labels  = $request->input('option_label', []);
        $images  = $request->file('option_image', []);
        $correct = (int) $request->input('option_correct', -1);

        return [
            'config'  => null,
            'options' => $this->collectOptions($labels, $images, fn($i) => $i === $correct),
        ];
    }

    public function renderAttemptInput(Question $question, string $inputName): string
    {
        return self::choiceMarkup($question, $inputName, multiple: false);
    }

    public function evaluate(Question $question, mixed $response): float
    {
        if (!$response) return 0;
        $option = $question->options->firstWhere('id', (int) $response);
        return $option && $option->is_correct ? (float) $question->marks : 0.0;
    }

    public function renderResponseSummary(Question $question, mixed $response): string
    {
        return self::summaryMarkup($question, is_array($response) ? $response : [$response]);
    }

    /* ---------- shared helpers used by single + multiple ---------- */

    public static function choiceMarkup(Question $question, string $inputName, bool $multiple): string
    {
        $type = $multiple ? 'checkbox' : 'radio';
        $name = $multiple ? $inputName . '[]' : $inputName;
        $required = $multiple ? '' : 'required';
        $html = '';
        foreach ($question->options as $option) {
            $img = $option->image_path
                ? '<img src="' . e(asset('storage/' . $option->image_path)) . '" alt="" class="opt-img">'
                : '';
            $label = $option->label !== null ? e($option->label) : '';
            $html .= sprintf(
                '<label class="option"><input type="%s" name="%s" value="%d" %s> %s %s</label>',
                $type, e($name), $option->id, $required, $img, $label
            );
        }
        return $html;
    }

    public static function summaryMarkup(Question $question, array $responseIds): string
    {
        $picked = collect($responseIds)->map(fn($v) => (int) $v)->filter()->all();
        if (empty($picked)) {
            return '<em class="muted">No response</em>';
        }
        $html = '';
        foreach ($picked as $id) {
            $option = $question->options->firstWhere('id', $id);
            if (!$option) continue;
            $cls = $option->is_correct ? 'correct' : 'incorrect';
            $html .= '<span class="pill ' . $cls . '">' . e($option->label ?? '(image)') . '</span>';
        }
        return $html;
    }

    protected function optionsEditorMarkup(string $kind): string
    {
        $inputType = $kind === 'multiple' ? 'checkbox' : 'radio';
        $name      = $kind === 'multiple' ? 'option_correct[]' : 'option_correct';
        return <<<HTML
        <div class="field">
            <label>Options (mark the correct one{$this->plural($kind)})</label>
            <div id="options-list">
                {$this->optionRow(0, $inputType, $name)}
                {$this->optionRow(1, $inputType, $name)}
            </div>
            <button type="button" class="btn-secondary" onclick="addOptionRow('{$inputType}','{$name}')">+ Add option</button>
        </div>
        <script>
        function addOptionRow(type, name) {
            const wrap = document.getElementById('options-list');
            const idx = wrap.querySelectorAll('.option-row').length;
            const row = document.createElement('div');
            row.className = 'option-row';
            row.innerHTML =
                '<input type="' + type + '" name="' + name + '" value="' + idx + '">' +
                ' <input type="text" name="option_label[]" placeholder="Option label">' +
                ' <input type="file" name="option_image[]" accept="image/*">';
            wrap.appendChild(row);
        }
        </script>
        HTML;
    }

    private function optionRow(int $idx, string $type, string $name): string
    {
        return <<<HTML
        <div class="option-row">
            <input type="{$type}" name="{$name}" value="{$idx}">
            <input type="text" name="option_label[]" placeholder="Option label">
            <input type="file" name="option_image[]" accept="image/*">
        </div>
        HTML;
    }

    private function plural(string $kind): string
    {
        return $kind === 'multiple' ? 's' : '';
    }

    protected function collectOptions(array $labels, array|null $images, callable $isCorrect): array
    {
        $images = $images ?? [];
        $rows = [];
        foreach ($labels as $i => $label) {
            $hasFile = isset($images[$i]) && $images[$i] && $images[$i]->isValid();
            $hasText = trim((string) $label) !== '';
            if (!$hasFile && !$hasText) continue;

            $imagePath = null;
            if ($hasFile) {
                $imagePath = $images[$i]->store('uploads', 'public');
            }
            $rows[] = [
                'label'      => $hasText ? trim($label) : null,
                'image_path' => $imagePath,
                'is_correct' => (bool) $isCorrect($i),
                'position'   => count($rows),
            ];
        }
        return $rows;
    }
}
