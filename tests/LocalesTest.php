<?php

namespace Tests;

use Astrotomic\Translatable\Exception\LocalesNotDefinedException;
use Astrotomic\Translatable\Locales;
use PHPUnit\Framework\Attributes\Test;

final class LocalesTest extends TestCase
{
    #[Test]
    public function locales_is_declared_as_a_singleton_instance(): void
    {
        $singletonHash = spl_object_hash(app(Locales::class));

        self::assertEquals($singletonHash, spl_object_hash($this->app->make('translatable.locales')));
        self::assertEquals($singletonHash, spl_object_hash($this->app->make(Locales::class)));
    }

    #[Test]
    public function it_loads_the_locales_from_the_configuration(): void
    {
        $this->app['config']->set('translatable.locales', [
            'de',
        ]);
        $this->app->make('translatable.locales')->load();
        self::assertEquals(['de'], $this->app->make('translatable.locales')->all());

        $this->app['config']->set('translatable.locales', [
            'de',
            'en',
        ]);
        self::assertEquals(['de'], $this->app->make('translatable.locales')->all());
        $this->app->make('translatable.locales')->load();
        self::assertEquals(['de', 'en'], $this->app->make('translatable.locales')->all());
    }

    #[Test]
    public function it_throws_an_exception_if_there_are_no_locales(): void
    {
        $this->expectException(LocalesNotDefinedException::class);

        $this->app['config']->set('translatable.locales', []);
        $this->app->make('translatable.locales')->load();
    }

    #[Test]
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

        self::assertEquals(['el', 'en', 'fr', 'de', 'id'], $this->app->make('translatable.locales')->all());
    }

    #[Test]
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

        self::assertEquals(['en', 'en-GB', 'en-US', 'de', 'de-DE', 'de-CH'], $this->app->make('translatable.locales')->all());
    }

    #[Test]
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

        self::assertEquals(['el', 'en', 'fr', 'de', 'id'], $this->app->make('translatable.locales')->toArray());
    }

    #[Test]
    public function can_retrieve_current_configuration(): void
    {
        $this->app['config']->set('translatable.locale', 'de');

        self::assertEquals('de', $this->app->make('translatable.locales')->current());
    }

    #[Test]
    public function current_can_return_the_translator_locale_if_configuration_is_empty(): void
    {
        $this->app['config']->set('translatable.locale', null);
        $this->app['translator']->setLocale('en');

        self::assertEquals('en', $this->app->make('translatable.locales')->current());
    }

    #[Test]
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

        self::assertTrue($this->app->make('translatable.locales')->has('de'));
        self::assertFalse($this->app->make('translatable.locales')->has('jp'));
    }

    #[Test]
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

        self::assertTrue(isset($this->app->make('translatable.locales')['de']));
        self::assertFalse(isset($this->app->make('translatable.locales')['jp']));
    }

    #[Test]
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

        self::assertEquals('de', $this->app->make('translatable.locales')->get('de'));
        self::assertNull($this->app->make('translatable.locales')->get('jp'));
    }

    #[Test]
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

        self::assertEquals('de', $this->app->make('translatable.locales')['de']);
        self::assertNull($this->app->make('translatable.locales')['jp']);
    }

    #[Test]
    public function it_can_add_a_locale(): void
    {
        $this->app['config']->set('translatable.locales', [
            'de',
        ]);
        $this->app->make('translatable.locales')->load();

        self::assertTrue($this->app->make('translatable.locales')->has('de'));
        self::assertFalse($this->app->make('translatable.locales')->has('en'));
        $this->app->make('translatable.locales')->add('en');
        self::assertTrue($this->app->make('translatable.locales')->has('en'));
    }

    #[Test]
    public function locale_can_be_added_by_accessing_as_an_array(): void
    {
        $this->app['config']->set('translatable.locales', [
            'de',
        ]);
        $this->app->make('translatable.locales')->load();

        self::assertTrue($this->app->make('translatable.locales')->has('de'));
        self::assertFalse($this->app->make('translatable.locales')->has('en'));
        $this->app->make('translatable.locales')[] = 'en';
        self::assertTrue($this->app->make('translatable.locales')->has('en'));
    }

    #[Test]
    public function locale_country_can_be_added_by_accessing_as_an_array(): void
    {
        $this->app['config']->set('translatable.locales', [
            'de',
        ]);
        $this->app->make('translatable.locales')->load();

        self::assertTrue($this->app->make('translatable.locales')->has('de'));
        self::assertFalse($this->app->make('translatable.locales')->has('de-AT'));
        $this->app->make('translatable.locales')['de'] = 'AT';
        self::assertTrue($this->app->make('translatable.locales')->has('de-AT'));
    }

    #[Test]
    public function can_forget_a_locale(): void
    {
        $this->app['config']->set('translatable.locales', [
            'de',
            'en',
        ]);
        $this->app->make('translatable.locales')->load();

        self::assertTrue($this->app->make('translatable.locales')->has('de'));
        self::assertTrue($this->app->make('translatable.locales')->has('en'));
        $this->app->make('translatable.locales')->forget('en');
        self::assertFalse($this->app->make('translatable.locales')->has('en'));
    }

    #[Test]
    public function can_forget_a_locale_using_unset_as_an_array(): void
    {
        $this->app['config']->set('translatable.locales', [
            'de',
            'en',
        ]);
        $this->app->make('translatable.locales')->load();

        self::assertTrue($this->app->make('translatable.locales')->has('de'));
        self::assertTrue($this->app->make('translatable.locales')->has('en'));
        unset($this->app->make('translatable.locales')['en']);
        self::assertFalse($this->app->make('translatable.locales')->has('en'));
    }

    #[Test]
    public function can_retrieve_the_locale_country_separator(): void
    {
        $this->app['config']->set('translatable.locale_separator', '_');

        self::assertEquals('_', $this->app->make('translatable.locales')->getLocaleSeparator());
    }

    #[Test]
    public function can_set_a_default_locale_country_separator_if_configuration_is_missing(): void
    {
        $this->app['config']->set('translatable.locale_separator', null);

        self::assertEquals('-', $this->app->make('translatable.locales')->getLocaleSeparator());
    }

    #[Test]
    public function can_get_a_country_locale_formatted_with_separator(): void
    {
        self::assertEquals('de-AT', $this->app->make('translatable.locales')->getCountryLocale('de', 'AT'));
    }

    #[Test]
    public function can_determine_if_a_locale_is_country_based(): void
    {
        self::assertTrue($this->app->make('translatable.locales')->isLocaleCountryBased('de-AT'));
        self::assertFalse($this->app->make('translatable.locales')->isLocaleCountryBased('de'));
    }

    #[Test]
    public function can_get_a_locale_from_the_country_locale(): void
    {
        self::assertEquals('de', $this->app->make('translatable.locales')->getLanguageFromCountryBasedLocale('de-AT'));
        self::assertEquals('de', $this->app->make('translatable.locales')->getLanguageFromCountryBasedLocale('de'));
    }
}
