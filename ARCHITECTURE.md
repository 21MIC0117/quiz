# Architecture

## Goal

Build a flexible quiz system on Laravel where new question types can be added with a single class, and where evaluation logic isn't sprinkled across the codebase.

## Stack choices

- **Laravel 11** — modern, well-known PHP framework. The assignment evaluates exactly the patterns Laravel encourages: layered controllers, Eloquent relationships, Blade composition, service providers for plugins.
- **SQLite via Eloquent** — zero-setup file database, perfect for an assignment, ships with Laravel as the default.
- **PHP built-in server** — used by `php artisan serve`; no Apache/Nginx config required.

## Layered structure

```
HTTP request
   │
   ▼
public/index.php  ── Laravel front controller
   │
   ▼
routes/web.php  ── matches METHOD + path → controller method
   │
   ▼
Controller  ── pulls request data, talks to Eloquent models, picks a Question Type, renders a Blade view
   │
   ▼
Eloquent model  ── relationships, casts, auto-timestamps
   │
   ▼
SQLite (database/database.sqlite)
```

Blade views under `resources/views/` extend a single `layouts/app.blade.php` master template.

## Data model

| Table     | Purpose                                                         |
| --------- | --------------------------------------------------------------- |
| `quizzes` | quiz metadata                                                   |
| `questions` | one row per question, including `type`, `prompt`, `marks`, `image_path`, `video_url`, and a `config` JSON column for type-specific settings |
| `options` | rows for choice-style questions (binary, single, multiple). Each has a label, optional image, and an `is_correct` flag. |
| `attempts` | one row per quiz attempt with `total_score` and `max_score`     |
| `answers` | per-attempt, per-question response stored as JSON (cast to array on read), plus the `awarded_marks` for that response |

Two columns deserve a note:

- `questions.config` (JSON) holds whatever type-specific settings the type needs (e.g. for `number_input`: `{correct, tolerance}`; for `text_input`: `{correct, case_sensitive}`). The schema doesn't grow when a new type is added.
- `answers.response` (JSON) stores the raw submitted response. Because the response is a JSON-encoded scalar/array, every type can be evaluated and rendered uniformly without per-type columns.

`->cascadeOnDelete()` on every foreign key keeps cleanup simple — deleting a quiz deletes its questions, options, attempts, and answers.

Eloquent relationships defined on the models:

- `Quiz` `hasMany` `Question` (ordered) and `Attempt` (newest first)
- `Question` `belongsTo` `Quiz` and `hasMany` `Option` (ordered)
- `Attempt` `belongsTo` `Quiz` and `hasMany` `Answer`
- `Answer` `belongsTo` `Attempt` and `Question`

## The Question Type plugin contract

This is the assignment's central design problem and the heart of the design.

```php
interface QuestionTypeInterface
{
    public function key(): string;                 // 'single_choice'
    public function label(): string;               // 'Single Choice'
    public function usesOptions(): bool;           // do we write rows in `options`?
    public function renderEditorFields(): string;
    public function buildFromRequest(Request $r): array;   // {config, options}
    public function renderAttemptInput(Question $q, string $name): string;
    public function evaluate(Question $q, mixed $response): float;
    public function renderResponseSummary(Question $q, mixed $response): string;
}
```

A type owns four lifecycle moments:

1. **Editor** — what extra fields show in the question creator (correct number, tolerance, options list, etc.)
2. **Persistence** — translating the submitted form into a `config` array and `options` rows.
3. **Attempt** — what the candidate sees during the quiz.
4. **Evaluation** — given the candidate's response, return the marks. Plus a small render helper for the result page.

`QuestionTypeRegistry` is bound as a singleton by `QuestionTypeServiceProvider`. Every type is registered there. Controllers ask for the registry through Laravel's service container (constructor injection).

### Why this matters (extensibility)

The assignment explicitly forbids "hardcoded or non-extensible logic". Concretely:

- The **controllers don't know** which type they're handling — they just call `$type->buildFromRequest()` and `$type->evaluate()`.
- The **Blade views don't know** either — they render whatever `$type->renderEditorFields()`, `$type->renderAttemptInput()`, and `$type->renderResponseSummary()` return.
- The **evaluator** is a 4-line loop in `AttemptController::submit()` that delegates to `$type->evaluate()`.
- The **database schema** doesn't change when a new type is added — `config` and `response` JSON columns absorb anything new.

Adding a new type is exactly two steps:

1. Add a class under `app/QuestionTypes/` implementing the interface.
2. Add a single `$registry->register(new MyType())` line in `QuestionTypeServiceProvider`.

No `switch` statement, no Blade branching, no migration.

### Worked example: imagine adding `OrderingType` (drag to reorder)

- Implement the interface — store the correct order in `config`, render an editor that lets the author list items, render an attempt UI (e.g., numbered selects), and evaluate by comparing arrays.
- Register it. Done.

The rest of the system — quizzes, attempts, scoring, results — needs zero changes.

## Evaluation logic

`AttemptController::submit()` is intentionally trivial:

```php
foreach ($quiz->questions as $q) {
    $maxScore += $q->marks;
    $type      = $this->registry->get($q->type);
    $response  = $request->input('answer_' . $q->id);
    $awarded   = $type->evaluate($q, $response);
    $totalScore += $awarded;
    $records[] = compact('q', 'response', 'awarded') + [...];
}
```

Per-type rules live inside each `evaluate()`:

- **Binary / Single Choice** — chosen option must be `is_correct`.
- **Multiple Choice** — sorted set of chosen option IDs must equal sorted set of correct option IDs (no partial credit, all-or-nothing).
- **Number Input** — `|response − correct| ≤ tolerance`.
- **Text Input** — exact match (case-sensitive optionally).

Each rule is local to its class so it's easy to find, change, and unit-test.

## Scores view

The quiz show page lists every attempt for that quiz in a Scores section: name (or "Anonymous"), score, percentage, submission timestamp, and a link to the full per-question breakdown. The data is loaded with the quiz via the `attempts` relationship and rendered alongside the quiz's questions.

## Media handling

- Images are uploaded as standard `multipart/form-data` and stored on the public disk via `$file->store('uploads', 'public')`. Only the relative path is stored in the DB; the public URL is `asset('storage/<path>')` thanks to `php artisan storage:link`.
- Video URLs are stored as plain strings — no embedding logic, just a link to the source (assignment explicitly mentions YouTube as the use case).

## Trade-offs and known limits

- No authentication — the assignment explicitly states it isn't required.
- No editing of an existing question (only create/delete). Editing would follow the same pattern: a `renderEditorFields($question)` overload that pre-fills values, plus an `update` controller method.
- No partial credit on `multiple_choice` — common requirement variants (e.g. proportional marks per correct selection) would be a single change inside `MultipleChoiceType::evaluate()` without touching anything else.
- Single shared layout template; no front-end build pipeline (no Vite or Tailwind) so the focus stays on the back-end design.
