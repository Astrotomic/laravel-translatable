<?php

namespace Astrotomic\Translatable\Tests;

use Astrotomic\Translatable\Tests\Eloquent\Country;
use Astrotomic\Translatable\Tests\Eloquent\Vegetable;

final class ScopesTest extends TestCase
{
    /** @test */
    public function translated_in_scope_returns_only_translated_records_for_this_locale(): void
    {
        factory(Country::class)->create(['code' => 'ca', 'name:ca' => 'Català']);
        factory(Country::class)->create(['code' => 'fr', 'name:fr' => 'Français']);

        static::assertEquals(1, Country::translatedIn('fr')->count());
    }

    /** @test */
    public function translated_in_scope_works_with_default_locale(): void
    {
        app()->setLocale('de');
        factory(Country::class)->create(['code' => 'ca', 'name:ca' => 'Català']);
        factory(Country::class)->create(['code' => 'el', 'name:de' => 'Griechenland']);

        static::assertEquals(1, Country::translatedIn()->count());
        static::assertEquals('Griechenland', Country::translatedIn()->first()->name);
    }

    /** @test */
    public function not_translated_in_scope_returns_only_not_translated_records_for_this_locale(): void
    {
        factory(Country::class)->create(['code' => 'ca', 'name:ca' => 'Català']);
        factory(Country::class)->create(['code' => 'fr', 'name:fr' => 'Français']);
        factory(Country::class)->create(['code' => 'en', 'name:en' => 'English']);

        $notTranslated = Country::notTranslatedIn('en')->get();

        static::assertEquals(2, $notTranslated->count());
        static::assertFalse($notTranslated->first()->hasTranslation('en'));
        static::assertFalse($notTranslated->last()->hasTranslation('en'));
    }

    /** @test */
    public function not_translated_in_scope_works_with_default_locale(): void
    {
        app()->setLocale('en');

        factory(Country::class)->create(['code' => 'ca', 'name:ca' => 'Català']);
        factory(Country::class)->create(['code' => 'en', 'name:es' => 'Inglés']);

        $notTranslated = Country::notTranslatedIn()->get();
        static::assertEquals(2, $notTranslated->count());
        static::assertFalse($notTranslated->first()->hasTranslation('en'));
    }

    /** @test */
    public function translated_scope_returns_records_with_at_least_one_translation(): void
    {
        factory(Country::class)->create(['code' => 'ca']);
        factory(Country::class)->create(['code' => 'en', 'name:en' => 'English']);

        static::assertEquals(1, Country::translated()->count());
        static::assertEquals('English', Country::with('translations')->translated()->first()->{'name:en'});
    }

    /** @test */
    public function lists_of_translated_fields(): void
    {
        app()->setLocale('de');
        app('config')->set('translatable.to_array_always_loads_translations', false);

        factory(Country::class)->create(['code' => 'el', 'name:de' => 'Griechenland']);
        factory(Country::class)->create(['code' => 'ca', 'name:ca' => 'Català']);

        $countries = Country::listsTranslations('name')->get();

        static::assertEquals(1, $countries->count());
        static::assertEquals(1, $countries->first()->id);
        static::assertEquals('Griechenland', $countries->first()->name);
    }

    /** @test */
    public function lists_of_translated_fields_with_fallback(): void
    {
        app('config')->set('translatable.fallback_locale', 'en');
        app('config')->set('translatable.to_array_always_loads_translations', false);
        app()->setLocale('de');

        factory(Country::class)->create(['code' => 'el', 'name:de' => 'Griechenland']);
        factory(Country::class)->create(['code' => 'fr', 'name:en' => 'France']);

        $country = new Country();
        $country->useTranslationFallback = true;

        $countries = $country->listsTranslations('name')->get();

        static::assertEquals(2, $countries->count());

        static::assertEquals(1, $countries->first()->id);
        static::assertEquals('Griechenland', $countries->first()->name);
        static::assertEquals('France', $countries->last()->name);
    }

    /** @test */
    public function lists_of_translated_fields_disable_autoload_translations(): void
    {
        app()->setLocale('de');
        app('config')->set('translatable.to_array_always_loads_translations', true);

        factory(Country::class)->create(['code' => 'el', 'name:de' => 'Griechenland']);

        Country::disableAutoloadTranslations();

        static::assertEquals([['id' => 1, 'name' => 'Griechenland']], Country::listsTranslations('name')->get()->toArray());
        Country::defaultAutoloadTranslations();
    }

    /** @test */
    public function lists_of_translated_fields_enable_autoload_translations(): void
    {
        app()->setLocale('de');
        app('config')->set('translatable.to_array_always_loads_translations', true);

        factory(Country::class)->create(['code' => 'el', 'name:de' => 'Griechenland']);

        Country::enableAutoloadTranslations();

        static::assertEquals([[
            'id' => 1,
            'name' => 'Griechenland',
            'translations' => [[
                'id' => 1,
                'country_id' => '1',
                'name' => 'Griechenland',
                'locale' => 'de',
            ]],
        ]], Country::listsTranslations('name')->get()->toArray());
        Country::defaultAutoloadTranslations();
    }

    /** @test */
    public function scope_withTranslation_without_fallback(): void
    {
        factory(Country::class)->create(['code' => 'el', 'name:en' => 'Greece']);

        $result = Country::withTranslation()->first();

        static::assertCount(1, $result->translations);
        static::assertSame('Greece', $result->translations->first()->name);
    }

    /** @test */
    public function scope_withTranslation_with_fallback(): void
    {
        app('config')->set('translatable.fallback_locale', 'de');
        app('config')->set('translatable.use_fallback', true);

        factory(Country::class)->create(['code' => 'el', 'name:en' => 'Greece', 'name:de' => 'Griechenland']);

        $result = Country::withTranslation()->first();
        static::assertEquals(2, $result->translations->count());
        static::assertEquals('Greece', $result->translations->where('locale', 'en')->first()->name);
        static::assertEquals('Griechenland', $result->translations->where('locale', 'de')->first()->name);
    }

    /** @test */
    public function scope_withTranslation_with_country_based_fallback(): void
    {
        app('config')->set('translatable.fallback_locale', 'en');
        app('config')->set('translatable.use_fallback', true);
        app()->setLocale('en-GB');

        factory(Vegetable::class)->create([
            'en' => ['name' => 'Zucchini'],
            'de' => ['name' => 'Zucchini'],
            'de-CH' => ['name' => 'Zucchetti'],
            'en-GB' => ['name' => 'Courgette'],
        ]);

        static::assertEquals('Courgette', Vegetable::withTranslation()->first()->name);

        app()->setLocale('de-CH');

        $translations = Vegetable::withTranslation()->first()->translations;

        static::assertEquals(3, $translations->count());

        static::assertEquals('de', $translations[0]->locale);
        static::assertEquals('Zucchini', $translations[0]->name);

        static::assertEquals('de-CH', $translations[1]->locale);
        static::assertEquals('Zucchetti', $translations[1]->name);

        static::assertEquals('en', $translations[2]->locale);
        static::assertEquals('Zucchini', $translations[2]->name);
    }

    /** @test */
    public function whereTranslation_filters_by_translation(): void
    {
        factory(Country::class)->create(['code' => 'gr', 'name:en' => 'Greece']);

        static::assertSame('gr', Country::whereTranslation('name', 'Greece')->first()->code);
    }

    /** @test */
    public function orWhereTranslation_filters_by_translation(): void
    {
        factory(Country::class)->create(['code' => 'gr', 'name:en' => 'Greece']);
        factory(Country::class)->create(['code' => 'fr', 'name:en' => 'France']);

        $result = Country::whereTranslation('name', 'Greece')->orWhereTranslation('name', 'France')->get();

        static::assertEquals(2, $result->count());
        static::assertSame('Greece', $result->first()->name);
        static::assertSame('France', $result->last()->name);
    }

    /** @test */
    public function whereTranslation_filters_by_translation_and_locale(): void
    {
        factory(Country::class)->create(['code' => 'gr', 'name:de' => 'Griechenland']);
        factory(Country::class)->create(['code' => 'some-code', 'name' => 'Griechenland']);

        static::assertEquals(2, Country::whereTranslation('name', 'Griechenland')->count());

        $result = Country::whereTranslation('name', 'Griechenland', 'de')->get();
        static::assertSame(1, $result->count());
        static::assertSame('gr', $result->first()->code);
    }

    /** @test */
    public function whereTranslationLike_filters_by_translation(): void
    {
        factory(Country::class)->create(['code' => 'gr', 'name:en' => 'Greece']);

        static::assertSame('gr', Country::whereTranslationLike('name', '%Greec%')->first()->code);
    }

    /** @test */
    public function orWhereTranslationLike_filters_by_translation(): void
    {
        factory(Country::class)->create(['code' => 'gr', 'name:en' => 'Greece']);
        factory(Country::class)->create(['code' => 'fr', 'name:en' => 'France']);

        $result = Country::whereTranslationLike('name', '%eece%')->orWhereTranslationLike('name', '%ance%')->get();

        static::assertEquals(2, $result->count());
        static::assertSame('Greece', $result->first()->name);
        static::assertSame('France', $result->last()->name);
    }

    /** @test */
    public function whereTranslationLike_filters_by_translation_and_locale(): void
    {
        factory(Country::class)->create(['code' => 'gr', 'name:de' => 'Griechenland']);
        factory(Country::class)->create(['code' => 'some-code', 'name:en' => 'Griechenland']);

        static::assertEquals(2, Country::whereTranslationLike('name', 'Griechen%')->count());

        $result = Country::whereTranslationLike('name', '%riechenlan%', 'de')->get();
        static::assertEquals(1, $result->count());
        static::assertEquals('gr', $result->first()->code);
    }

    /** @test */
    public function orderByTranslation_sorts_by_key_asc(): void
    {
        factory(Country::class)->create(['code' => 'el', 'name' => 'Greece']);
        factory(Country::class)->create(['code' => 'fr', 'name' => 'France']);

        static::assertEquals('fr', Country::orderByTranslation('name')->get()->first()->code);
    }

    /** @test */
    public function orderByTranslation_sorts_by_key_desc(): void
    {
        factory(Country::class)->create(['code' => 'el', 'name' => 'Greece']);
        factory(Country::class)->create(['code' => 'fr', 'name' => 'France']);

        static::assertEquals('el', Country::orderByTranslation('name', 'desc')->get()->first()->code);
    }

    /** @test */
    public function test_orderByTranslation_sorts_by_key_asc_even_if_locale_is_missing(): void
    {
        factory(Vegetable::class)->create(['en' => ['name' => 'Potatoes'], 'fr' => ['name' => 'Pommes de Terre']]);
        factory(Vegetable::class)->create(['en' => ['name' => 'Strawberries'], 'fr' => ['name' => 'Fraises']]);
        factory(Vegetable::class)->create([]);

        $orderInEnglish = Vegetable::orderByTranslation('name')->get();
        static::assertEquals([null, 'Potatoes', 'Strawberries'], $orderInEnglish->pluck('name')->toArray());

        app()->setLocale('fr');
        $orderInFrench = Vegetable::orderByTranslation('name', 'desc')->get();
        static::assertEquals(['Pommes de Terre', 'Fraises', null], $orderInFrench->pluck('name')->toArray());
    }
}
