<?php

use Orchestra\Testbench\TestCase;
use Illuminate\Contracts\Console\Kernel;
use Astrotomic\Translatable\TranslatableServiceProvider;

abstract class TestsBase extends TestCase
{
    protected $queriesCount;
    protected static $db2Setup = false;

    const DB_NAME = 'translatable_test';
    const DB_NAME2 = 'translatable_test2';
    const DB_USERNAME = 'homestead';
    const DB_PASSWORD = 'secret';

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate('mysql');
        $this->refreshSeedData();
    }

    protected function refreshSeedData()
    {
        $seeder = new AddFreshSeeds;
        $seeder->run();
    }

    protected function migrate($dbConnectionName)
    {
        $migrationsPath = '../../../../tests/migrations';
        $artisan = $this->app->make(Kernel::class);

        // Makes sure the migrations table is created
        $artisan->call('migrate:fresh', [
            '--database' => $dbConnectionName,
            '--path'     => $migrationsPath,
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            TranslatableServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['path.base'] = __DIR__.'/..';
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver'   => 'mysql',
            'host' => '127.0.0.1',
            'database' => static::DB_NAME,
            'username' => static::DB_USERNAME,
            'password' => static::DB_PASSWORD,
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'strict' => false,
        ]);
        $app['config']->set('database.connections.mysql2', [
            'driver'   => 'mysql',
            'host' => '127.0.0.1',
            'database' => static::DB_NAME2,
            'username' => static::DB_USERNAME,
            'password' => static::DB_PASSWORD,
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'strict' => false,
        ]);
        $locales = ['el', 'en', 'fr', 'de', 'id', 'en-GB', 'en-US', 'de-DE', 'de-CH'];
        $app['config']->set('translatable.locales', $locales);
    }
}
