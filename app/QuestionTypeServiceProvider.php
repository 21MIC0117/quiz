<?php

namespace App\Providers;

use App\QuestionTypes\BinaryType;
use App\QuestionTypes\MultipleChoiceType;
use App\QuestionTypes\NumberInputType;
use App\QuestionTypes\QuestionTypeRegistry;
use App\QuestionTypes\SingleChoiceType;
use App\QuestionTypes\TextInputType;
use Illuminate\Support\ServiceProvider;

class QuestionTypeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(QuestionTypeRegistry::class, function () {
            $registry = new QuestionTypeRegistry();
            $registry->register(new BinaryType());
            $registry->register(new SingleChoiceType());
            $registry->register(new MultipleChoiceType());
            $registry->register(new NumberInputType());
            $registry->register(new TextInputType());
            return $registry;
        });
    }

    public function boot(): void
    {
        //
    }
}
