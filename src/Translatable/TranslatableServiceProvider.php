<?php

namespace Astrotomic\Translatable;

use Illuminate\Support\ServiceProvider;

class TranslatableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/translatable.php' => config_path('translatable.php'),
        ], 'translatable');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/translatable.php', 'translatable'
        );

        $this->registerTranslatableHelper();
    }

    protected function registerTranslatableHelper()
    {
        $this->app->singleton('translatable.locales', Locales::class);
        $this->app->singleton(Locales::class);
    }
}
