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
        static::assertEquals('English', Country::translated()->first()->{'name:en'});
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
