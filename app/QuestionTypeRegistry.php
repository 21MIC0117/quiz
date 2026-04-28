<?php

namespace App\QuestionTypes;

use RuntimeException;

class QuestionTypeRegistry
{
    /** @var array<string, QuestionTypeInterface> */
    protected array $types = [];

    public function register(QuestionTypeInterface $type): void
    {
        $this->types[$type->key()] = $type;
    }

    /** @return array<string, QuestionTypeInterface> */
    public function all(): array
    {
        return $this->types;
    }

    public function get(string $key): QuestionTypeInterface
    {
        if (!isset($this->types[$key])) {
            throw new RuntimeException("Unknown question type: {$key}");
        }
        return $this->types[$key];
    }
}
