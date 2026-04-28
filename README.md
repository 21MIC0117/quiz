# Laravel Quiz

A small Laravel 11 application that lets you build quizzes, mix question types, take attempts, and see scored results. Uses SQLite for storage and the standard Laravel toolchain (Eloquent, Blade, Artisan).

## Features

- Create quizzes with title and description
- Add questions of five types — easily extensible to more:
  - Binary (True / False)
  - Single Choice
  - Multiple Choice
  - Number Input (with tolerance)
  - Text Input (case-sensitive optional)
- Rich-text question prompts, image upload, and YouTube/video URL per question
- Image-or-text options for choice questions
- Take a quiz and submit answers
- Automatic scoring (per-question marks, default 1)
- Results page with per-question breakdown
- Per-quiz Scores list showing every past attempt (name, score, %, timestamp) with a link to the full per-question breakdown

## Requirements

- PHP 8.2+ with the `pdo_sqlite`, `sqlite3`, `mbstring`, and `openssl` extensions
- [Composer](https://getcomposer.org/)

## Run locally

```bash
cd laravel-quiz
composer install
cp .env.example .env          # if .env doesn't already exist
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve --host=0.0.0.0 --port=8000
```

Then open http://localhost:8000.

> The project ships with a pre-generated `.env`, an empty SQLite database, and the storage symlink already created, so you usually only need `composer install` and `php artisan serve`.

## Project layout

```
laravel-quiz/
├── app/
│   ├── Http/Controllers/
│   │   ├── QuizController.php
│   │   ├── QuestionController.php
│   │   └── AttemptController.php
│   ├── Models/
│   │   ├── Quiz.php
│   │   ├── Question.php
│   │   ├── Option.php
│   │   ├── Attempt.php
│   │   └── Answer.php
│   ├── QuestionTypes/                  # extensible plugin layer
│   │   ├── QuestionTypeInterface.php
│   │   ├── AbstractQuestionType.php
│   │   ├── BinaryType.php
│   │   ├── SingleChoiceType.php
│   │   ├── MultipleChoiceType.php
│   │   ├── NumberInputType.php
│   │   ├── TextInputType.php
│   │   └── QuestionTypeRegistry.php
│   └── Providers/
│       └── QuestionTypeServiceProvider.php
├── database/
│   ├── migrations/                     # 5 quiz-domain migrations
│   └── database.sqlite                 # auto-created
├── resources/views/
│   ├── layouts/app.blade.php
│   ├── quizzes/{index,create,show}.blade.php
│   ├── questions/create.blade.php
│   └── attempts/{take,result}.blade.php
├── routes/web.php
└── public/style.css
```

## Adding a new question type

See `ARCHITECTURE.md` for the full design rationale. In short:

1. Create a new class under `app/QuestionTypes/` implementing `QuestionTypeInterface` (or extending `AbstractQuestionType`).
2. Register it inside `app/Providers/QuestionTypeServiceProvider::register()`.

That's the only change needed. Nothing in the controllers, Blade views, evaluator, or database touches type-specific code.
