<?php

use Astrotomic\Translatable\Test\Model\Food;
use Astrotomic\Translatable\Test\Model\Country;
use Astrotomic\Translatable\Test\Model\Vegetable;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class ScopesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function translated_in_scope_returns_only_translated_records_for_this_locale()
    {
        factory(Country::class)->create(['code' => 'ca', 'name:ca' => 'Català']);
        factory(Country::class)->create(['code' => 'fr', 'name:fr' => 'Français']);

        $this->assertEquals(1, Country::translatedIn('fr')->count());
    }

    /** @test */
    public function translated_in_scope_works_with_default_locale()
    {
        app()->setLocale('de');
        factory(Country::class)->create(['code' => 'ca', 'name:ca' => 'Català']);
        factory(Country::class)->create(['code' => 'el', 'name:de' => 'Griechenland']);

        $this->assertEquals(1, Country::translatedIn()->count());
        $this->assertEquals('Griechenland', Country::translatedIn()->first()->name);
    }

    /** @test */
    public function not_translated_in_scope_returns_only_not_translated_records_for_this_locale()
    {
        factory(Country::class)->create(['code' => 'ca', 'name:ca' => 'Català']);
        factory(Country::class)->create(['code' => 'fr', 'name:fr' => 'Français']);
        factory(Country::class)->create(['code' => 'en', 'name:en' => 'English']);

        $notTranslated = Country::notTranslatedIn('en')->get();

        $this->assertEquals(2, $notTranslated->count());
        $this->assertFalse($notTranslated->first()->hasTranslation('en'));
        $this->assertFalse($notTranslated->last()->hasTranslation('en'));
    }

    /** @test */
    public function not_translated_in_scope_works_with_default_locale()
    {
        app()->setLocale('en');

        factory(Country::class)->create(['code' => 'ca', 'name:ca' => 'Català']);
        factory(Country::class)->create(['code' => 'en', 'name:es' => 'Inglés']);

        $notTranslated = Country::notTranslatedIn()->get();
        $this->assertEquals(2, $notTranslated->count());
        $this->assertFalse($notTranslated->first()->hasTranslation('en'));
    }

    /** @test */
    public function translated_scope_returns_records_with_at_least_one_translation()
    {
        factory(Country::class)->create(['code' => 'ca']);
        factory(Country::class)->create(['code' => 'en', 'name:en' => 'English']);

        $this->assertEquals(1, Country::translated()->count());
        $this->assertEquals('English', Country::with('translations')->translated()->first()->{"name:en"});
    }

    /** @test */
    public function lists_of_translated_fields()
    {
        app()->setLocale('de');
        app('config')->set('translatable.to_array_always_loads_translations', false);

        factory(Country::class)->create(['code' => 'el', 'name:de' => 'Griechenland']);
        factory(Country::class)->create(['code' => 'ca', 'name:ca' => 'Català']);

        $countries = Country::listsTranslations('name')->get();

        $this->assertEquals(1, $countries->count());
        $this->assertEquals(1, $countries->first()->id);
        $this->assertEquals('Griechenland', $countries->first()->name);
    }

    /** @test */
    public function lists_of_translated_fields_with_fallback()
    {
        app('config')->set('translatable.fallback_locale', 'en');
        app('config')->set('translatable.to_array_always_loads_translations', false);
        app()->setLocale('de');

        factory(Country::class)->create(['code' => 'el', 'name:de' => 'Griechenland']);
        factory(Country::class)->create(['code' => 'fr', 'name:en' => 'France']);

        $country = new Country();
        $country->useTranslationFallback = true;

        $countries = $country->listsTranslations('name')->get();

        $this->assertEquals(2, $countries->count());

        $this->assertEquals(1, $countries->first()->id);
        $this->assertEquals('Griechenland', $countries->first()->name);
        $this->assertEquals('France', $countries->last()->name);
    }

    /** @test */
    public function lists_of_translated_fields_disable_autoload_translations()
    {
        app()->setLocale('de');
        app('config')->set('translatable.to_array_always_loads_translations', true);

        factory(Country::class)->create(['code' => 'el', 'name:de' => 'Griechenland']);

        Country::disableAutoloadTranslations();

        $this->assertEquals([['id' => 1, 'name' => 'Griechenland']], Country::listsTranslations('name')->get()->toArray());
        Country::defaultAutoloadTranslations();
    }

    /** @test */
    public function scope_withTranslation_without_fallback()
    {
        factory(Country::class)->create(['code' => 'el', 'name:en' => 'Greece']);

        $result = Country::withTranslation()->first();

        $this->assertCount(1, $result->translations);
        $this->assertSame('Greece', $result->translations->first()->name);
    }

    /** @test */
    public function scope_withTranslation_with_fallback()
    {
        app('config')->set('translatable.fallback_locale', 'de');
        app('config')->set('translatable.use_fallback', true);

        factory(Country::class)->create(['code' => 'el', 'name:en' => 'Greece', 'name:de' => 'Griechenland']);

        $result = Country::withTranslation()->first();
        $this->assertEquals(2, $result->translations->count());
        $this->assertEquals('Greece', $result->translations->where('locale', 'en')->first()->name);
        $this->assertEquals('Griechenland', $result->translations->where('locale', 'de')->first()->name);
    }

    /** @test */
    public function scope_withTranslation_with_country_based_fallback()
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

        $this->assertEquals('Courgette', Vegetable::withTranslation()->first()->name);

        app()->setLocale('de-CH');

        $translations = Vegetable::withTranslation()->first()->translations;

        $this->assertEquals(3, $translations->count());

        $this->assertEquals('de', $translations[0]->locale);
        $this->assertEquals('Zucchini', $translations[0]->name);

        $this->assertEquals('de-CH', $translations[1]->locale);
        $this->assertEquals('Zucchetti', $translations[1]->name);

        $this->assertEquals('en', $translations[2]->locale);
        $this->assertEquals('Zucchini', $translations[2]->name);
    }

    /** @test */
    public function whereTranslation_filters_by_translation()
    {
        factory(Country::class)->create(['code' => 'gr', 'name:en' => 'Greece']);

        $this->assertSame('gr', Country::whereTranslation('name', 'Greece')->first()->code);
    }

    /** @test */
    public function orWhereTranslation_filters_by_translation()
    {
        factory(Country::class)->create(['code' => 'gr', 'name:en' => 'Greece']);
        factory(Country::class)->create(['code' => 'fr', 'name:en' => 'France']);

        $result = Country::whereTranslation('name', 'Greece')->orWhereTranslation('name', 'France')->get();

        $this->assertEquals(2, $result->count());
        $this->assertSame('Greece', $result->first()->name);
        $this->assertSame('France', $result->last()->name);
    }

    /** @test */
    public function whereTranslation_filters_by_translation_and_locale()
    {
        factory(Country::class)->create(['code' => 'gr', 'name:de' => 'Griechenland']);
        factory(Country::class)->create(['code' => 'some-code', 'name' => 'Griechenland']);

        $this->assertEquals(2, Country::whereTranslation('name', 'Griechenland')->count());

        $result = Country::whereTranslation('name', 'Griechenland', 'de')->get();
        $this->assertSame(1, $result->count());
        $this->assertSame('gr', $result->first()->code);
    }

    /** @test */
    public function whereTranslationLike_filters_by_translation()
    {
        factory(Country::class)->create(['code' => 'gr', 'name:en' => 'Greece']);

        $this->assertSame('gr', Country::whereTranslationLike('name', '%Greec%')->first()->code);
    }

    /** @test */
    public function orWhereTranslationLike_filters_by_translation()
    {
        factory(Country::class)->create(['code' => 'gr', 'name:en' => 'Greece']);
        factory(Country::class)->create(['code' => 'fr', 'name:en' => 'France']);

        $result = Country::whereTranslationLike('name', '%eece%')->orWhereTranslationLike('name', '%ance%')->get();

        $this->assertEquals(2, $result->count());
        $this->assertSame('Greece', $result->first()->name);
        $this->assertSame('France', $result->last()->name);
    }

    /** @test */
    public function whereTranslationLike_filters_by_translation_and_locale()
    {
        factory(Country::class)->create(['code' => 'gr', 'name:de' => 'Griechenland']);
        factory(Country::class)->create(['code' => 'some-code', 'name:en' => 'Griechenland']);

        $this->assertEquals(2, Country::whereTranslationLike('name', 'Griechen%')->count());

        $result = Country::whereTranslationLike('name', '%riechenlan%', 'de')->get();
        $this->assertEquals(1, $result->count());
        $this->assertEquals('gr', $result->first()->code);
    }

    /** @test */
    public function orderByTranslation_sorts_by_key_asc()
    {
        factory(Country::class)->create(['code' => 'el', 'name' => 'Greece']);
        factory(Country::class)->create(['code' => 'fr', 'name' => 'France']);

        $this->assertEquals('fr', Country::orderByTranslation('name')->get()->first()->code);
    }

    /** @test */
    public function orderByTranslation_sorts_by_key_desc()
    {
        factory(Country::class)->create(['code' => 'el', 'name' => 'Greece']);
        factory(Country::class)->create(['code' => 'fr', 'name' => 'France']);

        $this->assertEquals('el', Country::orderByTranslation('name', 'desc')->get()->first()->code);
    }

    public function test_orderByTranslation_sorts_by_key_asc_even_if_locale_is_missing()
    {
        Food::create(['en' => ['name' => 'Potatoes'], 'fr' => ['name' => 'Pommes de Terre']]);
        Food::create(['en' => ['name' => 'Strawberries'], 'fr' => ['name' => 'Fraises']]);
        Food::create([]);

        $orderInEnglish = Food::with('translations')->orderByTranslation('name')->get();
        $this->assertEquals([null, 'Potatoes', 'Strawberries'], $orderInEnglish->pluck('name')->toArray());

        App::setLocale('fr');
        $orderInFrench = Food::with('translations')->orderByTranslation('name', 'desc')->get();
        $this->assertEquals(['Pommes de Terre', 'Fraises', null], $orderInFrench->pluck('name')->toArray());
    }
}
