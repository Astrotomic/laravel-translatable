<?php

namespace Astrotomic\Translatable;

use Astrotomic\Translatable\Validation\Rules\TranslatableExists;
use Astrotomic\Translatable\Validation\Rules\TranslatableUnique;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rule;

class TranslatableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/translatable.php' => config_path('translatable.php'),
        ], 'translatable');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'translatable');
        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/translatable'),
        ], 'translatable-lang');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/translatable.php',
            'translatable'
        );

        Rule::macro('translatableUnique', function (string $model, string $field): TranslatableUnique {
            return new TranslatableUnique($model, $field);
        });
        Rule::macro('translatableExists', function (string $model, string $field): TranslatableExists {
            return new TranslatableExists($model, $field);
        });

        $this->registerTranslatableHelper();
    }

    protected function registerTranslatableHelper(): void
    {
        $this->app->singleton('translatable.locales', Locales::class);
        $this->app->singleton(Locales::class);
    }
}
