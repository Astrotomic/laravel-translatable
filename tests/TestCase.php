<?php

namespace Astrotomic\Translatable\Tests;

use Astrotomic\Translatable\TranslatableServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();

        $this->withFactories(realpath(__DIR__.'/factories'));
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('translatable.locales', ['el', 'en', 'fr', 'de', 'id', 'en-GB', 'en-US', 'de-DE', 'de-CH']);
    }

    protected function getPackageProviders($app)
    {
        return [
            TranslatableServiceProvider::class,
        ];
    }

    protected function createTables(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('country_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('country_id')->unsigned();
            $table->string('name');
            $table->string('locale')->index();

            $table->unique(['country_id', 'locale']);
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });

        Schema::create('vegetables', function (Blueprint $table) {
            $table->increments('identity');
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });

        Schema::create('vegetable_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vegetable_identity')->unsigned();
            $table->string('name')->nullable();
            $table->string('locale')->index();

            $table->unique(['vegetable_identity', 'locale']);
            $table->foreign('vegetable_identity')->references('identity')->on('vegetables');
        });

        Schema::create('people', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        Schema::create('person_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('person_id')->unsigned();
            $table->string('name');
            $table->string('locale')->index();

            $table->unique(['person_id', 'locale']);
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');
        });
    }
}
