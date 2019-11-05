<?php

namespace Astrotomic\Translatable\Tests;

use Astrotomic\Translatable\Exception\LocalesNotDefinedException;
use Astrotomic\Translatable\Locales;

final class LocalesTest extends TestCase
{
    /** @test */
    public function locales_is_declared_as_a_singleton_instance(): void
    {
        $singletonHash = spl_object_hash(app(Locales::class));

        static::assertEquals($singletonHash, spl_object_hash($this->app->make('translatable.locales')));
        static::assertEquals($singletonHash, spl_object_hash($this->app->make(Locales::class)));
    }

    /** @test */
    public function it_loads_the_locales_from_the_configuration(): void
    {
        $this->app['config']->set('translatable.locales', [
            'de',
        ]);
        $this->app->make('translatable.locales')->load();
        static::assertEquals(['de'], $this->app->make('translatable.locales')->all());

        $this->app['config']->set('translatable.locales', [
            'de',
            'en',
        ]);
        static::assertEquals(['de'], $this->app->make('translatable.locales')->all());
        $this->app->make('translatable.locales')->load();
        static::assertEquals(['de', 'en'], $this->app->make('translatable.locales')->all());
    }

    /** @test */
    public function it_throws_an_exception_if_there_are_no_locales(): void
    {
        $this->expectException(LocalesNotDefinedException::class);

        $this->app['config']->set('translatable.locales', []);
        $this->app->make('translatable.locales')->load();
    }

    /** @test */
    public function all_language_locales_are_loaded_from_the_configuration(): void
    {
        $this->app['config']->set('translatable.locales', [
            'el',
            'en',
            'fr',
            'de',
            'id',
        ]);
        $this->app->make('translatable.locales')->load();

        static::assertEquals(['el', 'en', 'fr', 'de', 'id'], $this->app->make('translatable.locales')->all());
    }

    /** @test */
    public function it_loads_locales_and_countries(): void
    {
        $this->app['config']->set('translatable.locales', [
            'en' => [
                'GB',
                'US',
            ],
            'de' => [
                'DE',
                'CH',
            ],
        ]);
        $this->app->make('translatable.locales')->load();

        static::assertEquals(['en', 'en-GB', 'en-US', 'de', 'de-DE', 'de-CH'], $this->app->make('translatable.locales')->all());
    }

    /** @test */
    public function can_return_locales_as_array(): void
    {
        $this->app['config']->set('translatable.locales', [
            'el',
            'en',
            'fr',
            'de',
            'id',
        ]);
        $this->app->make('translatable.locales')->load();

        static::assertEquals(['el', 'en', 'fr', 'de', 'id'], $this->app->make('translatable.locales')->toArray());
    }

    /** @test */
    public function can_retrieve_current_configuration(): void
    {
        $this->app['config']->set('translatable.locale', 'de');

        static::assertEquals('de', $this->app->make('translatable.locales')->current());
    }

    /** @test */
    public function current_can_return_the_translator_locale_if_configuration_is_empty(): void
    {
        $this->app['config']->set('translatable.locale', null);
        $this->app['translator']->setLocale('en');

        static::assertEquals('en', $this->app->make('translatable.locales')->current());
    }

    /** @test */
    public function it_checks_if_it_has_a_locale(): void
    {
        $this->app['config']->set('translatable.locales', [
            'el',
            'en',
            'fr',
            'de',
            'id',
        ]);
        $this->app->make('translatable.locales')->load();

        static::assertTrue($this->app->make('translatable.locales')->has('de'));
        static::assertFalse($this->app->make('translatable.locales')->has('jp'));
    }

    /** @test */
    public function can_access_as_an_array(): void
    {
        $this->app['config']->set('translatable.locales', [
            'el',
            'en',
            'fr',
            'de',
            'id',
        ]);
        $this->app->make('translatable.locales')->load();

        static::assertTrue(isset($this->app->make('translatable.locales')['de']));
        static::assertFalse(isset($this->app->make('translatable.locales')['jp']));
    }

    /** @test */
    public function can_retrieve_a_specific_locale_by_get(): void
    {
        $this->app['config']->set('translatable.locales', [
            'el',
            'en',
            'fr',
            'de',
            'id',
        ]);
        $this->app->make('translatable.locales')->load();

        static::assertEquals('de', $this->app->make('translatable.locales')->get('de'));
        static::assertNull($this->app->make('translatable.locales')->get('jp'));
    }

    /** @test */
    public function missing_locale_returns_null_by_get(): void
    {
        $this->app['config']->set('translatable.locales', [
            'el',
            'en',
            'fr',
            'de',
            'id',
        ]);
        $this->app->make('translatable.locales')->load();

        static::assertEquals('de', $this->app->make('translatable.locales')['de']);
        static::assertNull($this->app->make('translatable.locales')['jp']);
    }

    /** @test */
    public function it_can_add_a_locale(): void
    {
        $this->app['config']->set('translatable.locales', [
            'de',
        ]);
        $this->app->make('translatable.locales')->load();

        static::assertTrue($this->app->make('translatable.locales')->has('de'));
        static::assertFalse($this->app->make('translatable.locales')->has('en'));
        $this->app->make('translatable.locales')->add('en');
        static::assertTrue($this->app->make('translatable.locales')->has('en'));
    }

    /** @test */
    public function locale_can_be_added_by_accessing_as_an_array(): void
    {
        $this->app['config']->set('translatable.locales', [
            'de',
        ]);
        $this->app->make('translatable.locales')->load();

        static::assertTrue($this->app->make('translatable.locales')->has('de'));
        static::assertFalse($this->app->make('translatable.locales')->has('en'));
        $this->app->make('translatable.locales')[] = 'en';
        static::assertTrue($this->app->make('translatable.locales')->has('en'));
    }

    /** @test */
    public function locale_country_can_be_added_by_accessing_as_an_array(): void
    {
        $this->app['config']->set('translatable.locales', [
            'de',
        ]);
        $this->app->make('translatable.locales')->load();

        static::assertTrue($this->app->make('translatable.locales')->has('de'));
        static::assertFalse($this->app->make('translatable.locales')->has('de-AT'));
        $this->app->make('translatable.locales')['de'] = 'AT';
        static::assertTrue($this->app->make('translatable.locales')->has('de-AT'));
    }

    /** @test */
    public function can_forget_a_locale(): void
    {
        $this->app['config']->set('translatable.locales', [
            'de',
            'en',
        ]);
        $this->app->make('translatable.locales')->load();

        static::assertTrue($this->app->make('translatable.locales')->has('de'));
        static::assertTrue($this->app->make('translatable.locales')->has('en'));
        $this->app->make('translatable.locales')->forget('en');
        static::assertFalse($this->app->make('translatable.locales')->has('en'));
    }

    /** @test */
    public function can_forget_a_locale_using_unset_as_an_array(): void
    {
        $this->app['config']->set('translatable.locales', [
            'de',
            'en',
        ]);
        $this->app->make('translatable.locales')->load();

        static::assertTrue($this->app->make('translatable.locales')->has('de'));
        static::assertTrue($this->app->make('translatable.locales')->has('en'));
        unset($this->app->make('translatable.locales')['en']);
        static::assertFalse($this->app->make('translatable.locales')->has('en'));
    }

    /** @test */
    public function can_retrieve_the_locale_country_separator(): void
    {
        $this->app['config']->set('translatable.locale_separator', '_');

        static::assertEquals('_', $this->app->make('translatable.locales')->getLocaleSeparator());
    }

    /** @test */
    public function can_set_a_default_locale_country_separator_if_configuration_is_missing(): void
    {
        $this->app['config']->set('translatable.locale_separator', null);

        static::assertEquals('-', $this->app->make('translatable.locales')->getLocaleSeparator());
    }

    /** @test */
    public function can_get_a_country_locale_formatted_with_separator(): void
    {
        static::assertEquals('de-AT', $this->app->make('translatable.locales')->getCountryLocale('de', 'AT'));
    }

    /** @test */
    public function can_determine_if_a_locale_is_country_based(): void
    {
        static::assertTrue($this->app->make('translatable.locales')->isLocaleCountryBased('de-AT'));
        static::assertFalse($this->app->make('translatable.locales')->isLocaleCountryBased('de'));
    }

    /** @test */
    public function can_get_a_locale_from_the_country_locale(): void
    {
        static::assertEquals('de', $this->app->make('translatable.locales')->getLanguageFromCountryBasedLocale('de-AT'));
        static::assertEquals('de', $this->app->make('translatable.locales')->getLanguageFromCountryBasedLocale('de'));
    }
}
