<?php

namespace App\QuestionTypes;

abstract class AbstractQuestionType implements QuestionTypeInterface
{
    public function usesOptions(): bool
    {
        return false;
    }

    public function renderEditorFields(): string
    {
        return '';
    }

    public function renderResponseSummary(\App\Models\Question $question, mixed $response): string
    {
        return '<em class="muted">No response</em>';
    }

    protected function e(?string $value): string
    {
        return e((string) $value);
    }
}
