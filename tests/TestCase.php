<?php

namespace Astrotomic\Translatable\Tests;

use Astrotomic\Translatable\TranslatableServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => realpath('tests/migrations'),
        ]);

        $this->withFactories(realpath('tests/factories'));
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
}
