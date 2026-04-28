# Architecture Overview

## Structure

The application follows Laravel’s MVC pattern:

* Models → Handle database interaction (Quiz, Question, Attempt, Answer, Option)
* Controllers → Handle request flow (QuizController, QuestionController, AttemptController)
* Views → Blade templates for UI

---

## Key Design Decisions

### 1. Separation of Question Types

Instead of handling all question logic in one place, different question types are implemented as separate classes:

* BinaryType
* SingleChoiceType
* MultipleChoiceType
* NumberInputType
* TextInputType

Each type follows a common interface.

This avoids large conditional blocks and makes the system easier to extend.

---

### 2. QuestionType Registry

A registry pattern is used (`QuestionTypeRegistry`) to map question types to their respective handlers.

This allows:

* Dynamic resolution of question logic
* Easier addition of new types without modifying existing code

---

### 3. Use of Interface and Abstract Class

* `QuestionTypeInterface` defines required behavior
* `AbstractQuestionType` provides shared logic

This reduces duplication and keeps implementations consistent.

---

### 4. Database Design

Main entities:

* Quiz → contains multiple questions
* Question → linked to quiz
* Option → used for choice-based questions
* Attempt → tracks quiz attempts
* Answer → stores user responses

Relationships are handled using Eloquent ORM.

---

### 5. Controller Responsibilities

Controllers handle:

* Request validation
* Passing data to models
* Coordinating flow between quiz creation and attempts

Some logic was simplified to keep controllers readable.

---

## Extensibility

The system is designed to allow:

* Adding new question types by creating a new class and registering it
* Extending quiz features without modifying existing structure
* Scaling logic into services if needed

---

## Limitations

* No dedicated service layer yet
* Limited validation in some areas
* UI is basic

---

## Future Improvements

* Introduce service layer for better separation
* Add API endpoints
* Improve validation and error handling
* Add user authentication

---
