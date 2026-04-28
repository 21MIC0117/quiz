# Laravel Quiz Application

## Overview

This is a Laravel-based quiz application that allows users to create quizzes, add different types of questions, and attempt quizzes.

The system supports multiple question formats such as:

* Single choice
* Multiple choice
* Binary (true/false)
* Text input
* Number input

The goal of this project was to build a flexible quiz system where question types can be extended without major changes to the core logic.

---

## Features

* Create and manage quizzes
* Add questions with different types
* Attempt quizzes
* Store user answers and attempts
* Display results after submission

---

## Tech Stack

* Backend: Laravel (PHP)
* Database: MySQL / SQLite
* Frontend: Blade templates
* ORM: Eloquent

---

## Setup Instructions

### Requirements

* PHP 8+
* Composer
* MySQL / SQLite

---

### Installation Steps

1. Clone the repository
   git clone https://github.com/your-username/laravel-quiz.git

2. Navigate into project
   cd laravel-quiz

3. Install dependencies
   composer install

4. Setup environment
   cp .env.example .env

5. Generate application key
   php artisan key:generate

6. Configure database in `.env.example`

7. Run migrations
   php artisan migrate

8. Start the server
   php artisan serve

---

## Usage

* Visit: http://localhost:8000
* Create a quiz
* Add questions
* Attempt the quiz
* View results

---

## Notes

* Question handling logic evolved during development, especially for supporting multiple types.
* Some parts were refactored to improve flexibility when adding new question formats.

---

## Future Improvements

* Add authentication system
* Improve UI/UX
* Add API support
* Add timer-based quizzes

---
