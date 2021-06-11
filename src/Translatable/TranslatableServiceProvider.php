<?php

namespace Astrotomic\Translatable;

use Illuminate\Database\Schema\Blueprint;
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
        $this->registerTranslatesMacro();
    }

    protected function registerTranslatableHelper()
    {
        $this->app->singleton('translatable.locales', Locales::class);
        $this->app->singleton(Locales::class);
    }

    protected function registerTranslatesMacro()
    {
        Blueprint::macro('translates', function ($table, $relationColumn = null) {
            if (is_null($relationColumn)) {
                $relationColumn = Str::singular($table).'_id';
            }

            $this->bigIncrements('id');
            $this->unsignedBigInteger($relationColumn)->unsigned()->index();
            $this->string('locale')->index();
            $this->unique([$relationColumn, 'locale']);
            $this->foreign($relationColumn)->references('id')->on($table)->onDelete('cascade');
        });
    }
}
