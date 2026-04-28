<?php

namespace App\QuestionTypes;

use App\Models\Question;
use Illuminate\Http\Request;

interface QuestionTypeInterface
{
    public function key(): string;

    public function label(): string;

    public function usesOptions(): bool;

    public function renderEditorFields(): string;

    /**
     * Translate the create-question form into a payload for persistence.
     * Returns ['config' => array|null, 'options' => array<int, array{label,is_correct,image_path?}>].
     */
    public function buildFromRequest(Request $request): array;

    public function renderAttemptInput(Question $question, string $inputName): string;

    /**
     * Score a candidate's response. Receives the raw value posted under "answer_{id}".
     */
    public function evaluate(Question $question, mixed $response): float;

    public function renderResponseSummary(Question $question, mixed $response): string;
}
