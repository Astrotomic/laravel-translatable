<?php

namespace Astrotomic\Translatable\Tests;

use Astrotomic\Translatable\Locales;
use Astrotomic\Translatable\Tests\Eloquent\Country;
use Astrotomic\Translatable\Tests\Eloquent\CountryStrict;
use Astrotomic\Translatable\Tests\Eloquent\CountryTranslation;
use Astrotomic\Translatable\Tests\Eloquent\Person;
use Astrotomic\Translatable\Tests\Eloquent\Vegetable;
use Astrotomic\Translatable\Tests\Eloquent\VegetableTranslation;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

final class TranslatableTest extends TestCase
{
    /** @test */
    public function it_finds_the_default_translation_class(): void
    {
        static::assertEquals(
            VegetableTranslation::class,
            (new Vegetable())->getTranslationModelNameDefault()
        );
    }

    /** @test */
    public function it_finds_the_translation_class_with_namespace_set(): void
    {
        $this->app->make('config')->set('translatable.translation_model_namespace', 'App\Models\Translations');

        static::assertEquals(
            'App\Models\Translations\VegetableTranslation',
            (new Vegetable())->getTranslationModelNameDefault()
        );
    }

    /** @test */
    public function it_finds_the_translation_class_with_suffix_set(): void
    {
        $this->app->make('config')->set('translatable.translation_suffix', 'Trans');

        static::assertEquals(
            'Astrotomic\Translatable\Tests\Eloquent\VegetableTrans',
            (new Vegetable())->getTranslationModelName()
        );
    }

    /** @test */
    public function it_returns_custom_TranslationModelName(): void
    {
        $vegetable = new Vegetable();

        static::assertEquals(
            $vegetable->getTranslationModelNameDefault(),
            $vegetable->getTranslationModelName()
        );

        $vegetable->translationModel = 'MyAwesomeVegetableTranslation';
        static::assertEquals(
            'MyAwesomeVegetableTranslation',
            $vegetable->getTranslationModelName()
        );
    }

    /** @test */
    public function it_returns_relation_key(): void
    {
        $vegetable = new Vegetable();
        static::assertEquals('vegetable_identity', $vegetable->getRelationKey());

        $vegetable->translationForeignKey = 'my_awesome_key';
        static::assertEquals('my_awesome_key', $vegetable->getRelationKey());
    }

    /** @test */
    public function it_returns_the_translation(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:el' => 'Αρακάς', 'name:en' => 'Peas']);

        static::assertEquals('Αρακάς', $vegetable->translate('el')->name);

        static::assertEquals('Peas', $vegetable->translate('en')->name);

        $this->app->setLocale('el');
        static::assertEquals('Αρακάς', $vegetable->translate()->name);

        $this->app->setLocale('en');
        static::assertEquals('Peas', $vegetable->translate()->name);
    }

    /** @test */
    public function it_returns_the_translation_with_accessor(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:el' => 'Αρακάς', 'name:en' => 'Peas']);

        static::assertEquals('Αρακάς', $vegetable->{'name:el'});
        static::assertEquals('Peas', $vegetable->{'name:en'});
    }

    /** @test */
    public function it_returns_null_when_the_locale_doesnt_exist(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:el' => 'Αρακάς']);

        static::assertSame(null, $vegetable->{'name:unknown-locale'});
    }

    /** @test */
    public function it_saves_translations(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:el' => 'Αρακάς', 'name:en' => 'Peas']);

        static::assertEquals('Peas', $vegetable->name);

        $vegetable->name = 'Pea';
        $vegetable->save();
        $vegetable->refresh();

        static::assertEquals('Pea', $vegetable->name);
    }

    /** @test */
    public function it_saves_translations_with_mutator(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:el' => 'Αρακάς', 'name:en' => 'Peas']);

        $vegetable->{'name:en'} = 'Pea';
        $vegetable->{'name:el'} = 'Μπιζέλι';
        $vegetable->save();
        $vegetable->refresh();

        $this->app->setLocale('en');
        static::assertEquals('Pea', $vegetable->translate()->name);

        $this->app->setLocale('el');
        static::assertEquals('Μπιζέλι', $vegetable->translate()->name);
    }

    /** @test */
    public function it_does_not_lazy_load_translations_when_updating_non_translated_attributes(): void
    {
        DB::enableQueryLog();

        $vegetable = factory(Vegetable::class)->create();
        static::assertFalse($vegetable->relationLoaded('translations'));
        static::assertCount(1, DB::getQueryLog());

        DB::flushQueryLog();

        $vegetable->update(['quantity' => 5]);
        static::assertFalse($vegetable->relationLoaded('translations'));
        static::assertCount(1, DB::getQueryLog());

        DB::flushQueryLog();

        $vegetable->update(['name' => 'Germany']);
        static::assertTrue($vegetable->relationLoaded('translations'));
        static::assertCount(2, DB::getQueryLog());
        DB::disableQueryLog();
    }

    /** @test */
    public function it_uses_default_locale_to_return_translations(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:el' => 'Αρακάς']);

        $vegetable->translate('el')->name = 'Μπιζέλι';

        $this->app->setLocale('el');
        static::assertEquals('Μπιζέλι', $vegetable->name);
        $vegetable->save();

        $vegetable->refresh();
        static::assertEquals('Μπιζέλι', $vegetable->translate('el')->name);
    }

    /** @test */
    public function it_creates_translations_using_the_shortcut(): void
    {
        $vegetable = factory(Vegetable::class)->create();

        $vegetable->name = 'Peas';
        $vegetable->save();

        $vegetable = Vegetable::first();
        static::assertEquals('Peas', $vegetable->name);
        static::assertDatabaseHas('vegetable_translations', [
            'vegetable_identity' => $vegetable->identity,
            'locale' => 'en',
            'name' => 'Peas',
        ]);
    }

    /** @test */
    public function it_creates_translations_using_mass_assignment(): void
    {
        $vegetable = Vegetable::create([
            'quantity' => 5,
            'name' => 'Peas',
        ]);

        static::assertEquals(5, $vegetable->quantity);
        static::assertEquals('Peas', $vegetable->name);
    }

    /** @test */
    public function it_creates_translations_using_mass_assignment_and_locales(): void
    {
        $vegetable = Vegetable::create([
            'quantity' => 5,
            'en'   => ['name' => 'Peas'],
            'fr'   => ['name' => 'Pois'],
        ]);

        static::assertEquals(5, $vegetable->quantity);
        static::assertEquals('Peas', $vegetable->translate('en')->name);
        static::assertEquals('Pois', $vegetable->translate('fr')->name);

        $vegetable = Vegetable::first();
        static::assertEquals('Peas', $vegetable->translate('en')->name);
        static::assertEquals('Pois', $vegetable->translate('fr')->name);
    }

    /** @test */
    public function it_skips_mass_assignment_if_attributes_non_fillable(): void
    {
        $this->expectException(MassAssignmentException::class);
        $country = CountryStrict::create([
            'code' => 'be',
            'en'   => ['name' => 'Belgium'],
            'fr'   => ['name' => 'Belgique'],
        ]);

        static::assertEquals('be', $country->code);
        static::assertNull($country->translate('en'));
        static::assertNull($country->translate('fr'));
    }

    /** @test */
    public function it_returns_if_object_has_translation(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:en' => 'Peas']);

        static::assertTrue($vegetable->hasTranslation('en'));
        static::assertFalse($vegetable->hasTranslation('some-code'));
    }

    /** @test */
    public function it_returns_default_translation(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'de');

        $vegetable = factory(Vegetable::class)->create(['name:de' => 'Erbsen']);

        static::assertEquals('Erbsen', $vegetable->getTranslation('ch', true)->name);
        static::assertEquals('Erbsen', $vegetable->translateOrDefault('ch')->name);
        static::assertNull($vegetable->getTranslation('ch', false));

        $this->app->setLocale('ch');
        static::assertSame('Erbsen', $vegetable->translateOrDefault()->name);
    }

    /** @test */
    public function fallback_option_in_config_overrides_models_fallback_option(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'de');

        $vegetable = factory(Vegetable::class)->create(['name:de' => 'Erbsen']);
        static::assertEquals('de', $vegetable->getTranslation('ch', true)->locale);

        $vegetable->useTranslationFallback = false;
        static::assertEquals('de', $vegetable->getTranslation('ch', true)->locale);

        $vegetable->useTranslationFallback = true;
        static::assertEquals('de', $vegetable->getTranslation('ch')->locale);

        $vegetable->useTranslationFallback = false;
        static::assertNull($vegetable->getTranslation('ch'));
    }

    /** @test */
    public function configuration_defines_if_fallback_is_used(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'de');
        $this->app->make('config')->set('translatable.use_fallback', true);

        $vegetable = factory(Vegetable::class)->create(['name:de' => 'Erbsen']);

        static::assertEquals('de', $vegetable->getTranslation('ch')->locale);
    }

    /** @test */
    public function useTranslationFallback_overrides_configuration(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'de');
        $this->app->make('config')->set('translatable.use_fallback', true);

        $vegetable = factory(Vegetable::class)->create(['name:en' => 'Peas']);
        $vegetable->useTranslationFallback = false;

        static::assertNull($vegetable->getTranslation('ch'));
    }

    /** @test */
    public function it_returns_null_if_fallback_is_not_defined(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'ch');

        $vegetable = factory(Vegetable::class)->create(['name:en' => 'Peas']);

        static::assertNull($vegetable->getTranslation('pl', true));
    }

    /** @test */
    public function it_fills_a_non_default_language_with_fallback_set(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'en');

        $vegetable = new Vegetable();
        $vegetable->fill([
            'quantity' => 5,
            'en'   => ['name' => 'Peas'],
            'de'   => ['name' => 'Erbsen'],
        ]);

        static::assertEquals('Peas', $vegetable->translate('en')->name);
    }

    /** @test */
    public function it_creates_a_new_translation(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'en');

        $vegetable = factory(Vegetable::class)->create();
        $vegetable->getNewTranslation('en')->name = 'Peas';
        $vegetable->save();

        static::assertEquals('Peas', $vegetable->translate('en')->name);
    }

    /** @test */
    public function the_locale_key_is_locale_by_default(): void
    {
        $vegetable = new Vegetable();

        static::assertEquals('locale', $vegetable->getLocaleKey());
    }

    /** @test */
    public function the_locale_key_can_be_overridden_in_configuration(): void
    {
        $this->app->make('config')->set('translatable.locale_key', 'language_id');

        $vegetable = new Vegetable();
        static::assertEquals('language_id', $vegetable->getLocaleKey());
    }

    /** @test */
    public function the_locale_key_can_be_customized_per_model(): void
    {
        $vegetable = new Vegetable();
        $vegetable->localeKey = 'language_id';
        static::assertEquals('language_id', $vegetable->getLocaleKey());
    }

    public function test_the_translation_model_can_be_customized(): void
    {
        CountryStrict::unguard();
        $country = CountryStrict::create([
            'code' => 'es',
            'name:en' => 'Spain',
            'name:de' => 'Spanien',
        ]);
        static::assertTrue($country->exists());
        static::assertEquals($country->translate('en')->name, 'Spain');
        static::assertEquals($country->translate('de')->name, 'Spanien');
        CountryStrict::reguard();
    }

    /** @test */
    public function it_reads_the_configuration(): void
    {
        static::assertEquals('Translation', $this->app->make('config')->get('translatable.translation_suffix'));
    }

    /** @test */
    public function getting_translation_does_not_create_translation(): void
    {
        $vegetable = factory(Vegetable::class)->create();

        static::assertNull($vegetable->getTranslation('en', false));
    }

    /** @test */
    public function getting_translated_field_does_not_create_translation(): void
    {
        $this->app->setLocale('en');
        $vegetable = factory(Vegetable::class)->create();

        static::assertNull($vegetable->getTranslation('en'));
    }

    /** @test */
    public function it_has_methods_that_return_always_a_translation(): void
    {
        $vegetable = factory(Vegetable::class)->create();
        static::assertEquals('abc', $vegetable->translateOrNew('abc')->locale);

        $this->app->setLocale('xyz');
        static::assertEquals('xyz', $vegetable->translateOrNew()->locale);
    }

    /** @test */
    public function it_throws_an_exception_if_translation_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage(sprintf('No query results for model [%s] %s', VegetableTranslation::class, 'xyz'));

        $vegetable = Vegetable::create([
            'en' => ['name' => 'Peas'],
        ]);
        static::assertEquals('en', $vegetable->translateOrFail('en')->locale);

        $vegetable->translateOrFail('xyz');
    }

    /** @test */
    public function it_returns_if_attribute_is_translated(): void
    {
        $vegetable = new Vegetable();

        static::assertTrue($vegetable->isTranslationAttribute('name'));
        static::assertFalse($vegetable->isTranslationAttribute('some-field'));
    }

    /** @test */
    public function config_overrides_apps_locale(): void
    {
        $veegtable = factory(Vegetable::class)->create(['name:de' => 'Erbsen']);
        App::make('config')->set('translatable.locale', 'de');

        static::assertEquals('Erbsen', $veegtable->name);
    }

    /** @test */
    public function locales_as_array_keys_are_properly_detected(): void
    {
        $this->app->config->set('translatable.locales', ['en' => ['US', 'GB']]);

        $vegetable = Vegetable::create([
            'en'    => ['name' => 'Peas'],
            'en-US' => ['name' => 'US Peas'],
            'en-GB' => ['name' => 'GB Peas'],
        ]);

        static::assertEquals('Peas', $vegetable->getTranslation('en')->name);
        static::assertEquals('GB Peas', $vegetable->getTranslation('en-GB')->name);
        static::assertEquals('US Peas', $vegetable->getTranslation('en-US')->name);
    }

    /** @test */
    public function locale_separator_can_be_configured(): void
    {
        $this->app->make('config')->set('translatable.locales', ['en' => ['GB']]);
        $this->app->make('config')->set('translatable.locale_separator', '_');
        $this->app->make('translatable.locales')->load();
        $vegetable = Vegetable::create([
            'en_GB' => ['name' => 'Peas'],
        ]);

        static::assertEquals('Peas', $vegetable->getTranslation('en_GB')->name);
    }

    /** @test */
    public function fallback_for_country_based_locales(): void
    {
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->make('config')->set('translatable.fallback_locale', 'fr');
        $this->app->make('config')->set('translatable.locales', ['en' => ['US', 'GB'], 'fr']);
        $this->app->make('config')->set('translatable.locale_separator', '-');
        $this->app->make('translatable.locales')->load();

        $vegetable = factory(Vegetable::class)->create([
            'fr'    => ['name' => 'Frites'],
            'en-GB' => ['name' => 'Chips'],
            'en'    => ['name' => 'French fries'],
        ]);

        static::assertEquals('French fries', $vegetable->getTranslation('en-US')->name);
    }

    /** @test */
    public function fallback_for_country_based_locales_with_no_base_locale(): void
    {
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->make('config')->set('translatable.fallback_locale', 'en');
        $this->app->make('config')->set('translatable.locales', ['pt' => ['PT', 'BR'], 'en']);
        $this->app->make('config')->set('translatable.locale_separator', '-');
        $this->app->make('translatable.locales')->load();

        $vegetable = factory(Vegetable::class)->create([
            'en'    => ['name' => 'Chips'],
            'pt-PT' => ['name' => 'Batatas fritas'],
        ]);

        static::assertEquals('Chips', $vegetable->getTranslation('pt-BR')->name);
    }

    /** @test */
    public function to_array_and_fallback_with_country_based_locales_enabled(): void
    {
        $this->app->make('config')->set('translatable.locale', 'en-GB');
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->make('config')->set('translatable.fallback_locale', 'fr');
        $this->app->make('config')->set('translatable.locales', ['en' => ['GB'], 'fr']);
        $this->app->make('config')->set('translatable.locale_separator', '-');
        $this->app->make('translatable.locales')->load();

        $vegetable = factory(Vegetable::class)->create(['name:fr' => 'Frites']);

        static::assertEquals('Frites', $vegetable['name']);
    }

    /** @test */
    public function it_skips_translations_in_to_array_when_config_is_set(): void
    {
        $this->app->make('config')->set('translatable.to_array_always_loads_translations', false);

        factory(Vegetable::class)->create(['name' => 'Peas']);

        $vegetable = Vegetable::first()->toArray();
        static::assertFalse(isset($vegetable['name']));
    }

    /** @test */
    public function it_returns_translations_in_to_array_when_config_is_set_but_translations_are_loaded(): void
    {
        $this->app->make('config')->set('translatable.to_array_always_loads_translations', false);
        factory(Vegetable::class)->create(['name' => 'Peas']);

        $vegetable = Vegetable::with('translations')->first()->toArray();

        static::assertTrue(isset($vegetable['name']));
    }

    /** @test */
    public function it_should_mutate_the_translated_attribute_if_a_mutator_is_set_on_model(): void
    {
        $person = new Person(['name' => 'john doe']);
        $person->save();
        $person = Person::find(1);
        static::assertEquals('John Doe', $person->name);
    }

    /** @test */
    public function it_deletes_all_translations(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:es' => 'Guisantes', 'name:en' => 'Peas']);

        static::assertEquals(2, count($vegetable->translations));

        $vegetable->deleteTranslations();

        static::assertEquals(0, count($vegetable->translations));
    }

    /** @test */
    public function it_deletes_translations_for_given_locales(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:es' => 'Guisantes', 'name:en' => 'Peas']);

        static::assertEquals(2, count($vegetable->translations));

        $vegetable->deleteTranslations('es');

        static::assertEquals(1, count($vegetable->translations));
    }

    /** @test */
    public function passing_an_empty_array_should_not_delete_translations(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:es' => 'Guisantes', 'name:en' => 'Peas']);

        static::assertEquals(2, count($vegetable->translations));

        $vegetable->deleteTranslations([]);

        static::assertEquals(2, count($vegetable->translations));
    }

    /** @test */
    public function fill_with_translation_key(): void
    {
        $vegetable = new Vegetable();
        $vegetable->fill([
            'name:en' => 'Peas',
            'name:de' => 'Erbsen',
        ]);
        static::assertEquals('Peas', $vegetable->translate('en')->name);
        static::assertEquals('Erbsen', $vegetable->translate('de')->name);

        $vegetable->save();
        $vegetable = Vegetable::first();
        static::assertEquals('Peas', $vegetable->translate('en')->name);
        static::assertEquals('Erbsen', $vegetable->translate('de')->name);
    }

    /** @test */
    public function it_uses_the_default_locale_from_the_model(): void
    {
        $vegetable = new Vegetable();
        $vegetable->fill([
            'name:en' => 'Peas',
            'name:fr' => 'Pois',
        ]);
        static::assertEquals('Peas', $vegetable->name);
        $vegetable->setDefaultLocale('fr');
        static::assertEquals('Pois', $vegetable->name);

        $vegetable->setDefaultLocale(null);

        $vegetable->save();
        $vegetable = Vegetable::first();

        static::assertEquals('Peas', $vegetable->name);
        $vegetable->setDefaultLocale('fr');
        static::assertEquals('Pois', $vegetable->name);
    }

    /** @test */
    public function replicate_entity(): void
    {
        $vegetable = new Vegetable();
        $vegetable->fill([
            'name:fr' => 'Pomme',
            'name:en' => 'Apple',
            'name:de' => 'Apfel',
        ]);
        $vegetable->save();

        $replicated = $vegetable->replicateWithTranslations();
        $replicated->save();

        static::assertNotNull($replicated->identity);
        static::assertNotEquals($replicated->identity, $vegetable->identity);
        static::assertEquals($replicated->translate('fr')->name, $vegetable->translate('fr')->name);
        static::assertEquals($replicated->translate('en')->name, $vegetable->translate('en')->name);
        static::assertEquals($replicated->translate('de')->name, $vegetable->translate('de')->name);

        static::assertNotNull($replicated->translate('fr')->vegetable_identity);
        static::assertNotEquals($replicated->translate('fr')->vegetable_identity, $vegetable->identity);
        static::assertEquals($replicated->translate('fr')->vegetable_identity, $replicated->identity);
        static::assertNotEquals($replicated->translate('en')->vegetable_identity, $vegetable->identity);
        static::assertEquals($replicated->translate('en')->vegetable_identity, $replicated->identity);
        static::assertNotEquals($replicated->translate('de')->vegetable_identity, $vegetable->identity);
        static::assertEquals($replicated->translate('de')->vegetable_identity, $replicated->identity);
    }

    /** @test */
    public function can_get_translations_as_array(): void
    {
        $vegetable = factory(Vegetable::class)->create([
            'name:en' => 'Peas',
            'name:fr' => 'Pois',
            'name:de' => 'Erbsen',
        ]);

        static::assertEquals([
            'de' => ['name' => 'Erbsen'],
            'en' => ['name' => 'Peas'],
            'fr' => ['name' => 'Pois'],
        ], $vegetable->getTranslationsArray());
    }

    /** @test */
    public function fill_will_ignore_unkown_locales(): void
    {
        config(['translatable.locales' => ['en']]);

        $vegetable = new Vegetable();
        $vegetable->fill([
            'en'   => ['name' => 'Peas'],
            'ua'   => ['name' => 'Unkown'],
        ]);
        $vegetable->save();

        static::assertDatabaseHas('vegetable_translations', [
            'locale' => 'en',
            'name' => 'Peas',
        ]);

        static::assertDatabaseMissing('vegetable_translations', ['locale' => 'ua']);
    }

    /** @test */
    public function fill_will_ignore_unkown_locales_with_translations(): void
    {
        config(['translatable.locales' => ['en']]);

        $vegetable = new Vegetable();
        $vegetable->fill([
            'name:en' => 'Peas',
            'name:ua' => 'Unkown',
        ]);

        $vegetable->save();

        static::assertDatabaseHas('vegetable_translations', [
            'locale' => 'en',
            'name' => 'Peas',
        ]);

        static::assertDatabaseMissing('vegetable_translations', ['locale' => 'ua']);
    }

    /** @test */
    public function it_uses_fallback_locale_if_default_is_empty(): void
    {
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->make('config')->set('translatable.use_property_fallback', true);
        $this->app->make('config')->set('translatable.fallback_locale', 'en');
        $vegetable = new Vegetable();
        $vegetable->fill([
            'name:en' => 'Peas',
            'name:fr' => '',
        ]);

        $this->app->setLocale('en');
        static::assertEquals('Peas', $vegetable->name);
        $this->app->setLocale('fr');
        static::assertEquals('Peas', $vegetable->name);
    }

    /** @test */
    public function it_uses_value_when_fallback_is_not_available(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'it');
        $this->app->make('config')->set('translatable.use_fallback', true);

        $vegetable = new Vegetable();
        $vegetable->fill([
            'en' => ['name' => ''],
            'de' => ['name' => 'Erbsen'],
        ]);

        // verify translated attributed is correctly returned when empty (non-existing fallback is ignored)
        $this->app->setLocale('en');
        static::assertEquals('', $vegetable->getAttribute('name'));

        $this->app->setLocale('de');
        static::assertEquals('Erbsen', $vegetable->getAttribute('name'));
    }

    /** @test */
    public function empty_translated_attribute(): void
    {
        $this->app->setLocale('invalid');
        $vegetable = factory(Vegetable::class)->create();

        static::assertNull($vegetable->name);
    }

    /** @test */
    public function numeric_translated_attribute(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'de');
        $this->app->make('config')->set('translatable.use_fallback', true);

        $vegetable = new class extends Vegetable {
            protected $table = 'vegetables';
            public $translationModel = VegetableTranslation::class;

            protected function isEmptyTranslatableAttribute(string $key, $value): bool
            {
                if ($key === 'name') {
                    return is_null($value);
                }

                return empty($value);
            }
        };

        $vegetable->fill([
            'en' => ['name' => '0'],
            'de' => ['name' => '1'],
            'fr' => ['name' => null],
        ]);
        $vegetable->save();

        $this->app->setLocale('en');
        static::assertSame('0', $vegetable->name);

        $this->app->setLocale('fr');
        static::assertSame('1', $vegetable->name);
    }

    /** @test */
    public function translation_relation(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'fr');
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->setLocale('en');

        $peas = factory(Vegetable::class)->create(['name:en' => 'Peas']);

        static::assertInstanceOf(VegetableTranslation::class, $peas->translation);
        static::assertEquals('en', $peas->translation->locale);
    }

    /** @test */
    public function translation_relation_can_use_fallback_locale(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'fr');
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->setLocale('en');

        $peas = factory(Vegetable::class)->create(['name:fr' => 'Pois']);

        static::assertEquals('fr', $peas->translation->locale);
    }

    /** @test */
    public function translation_relation_returns_null_if_no_available_locale_was_found(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'xyz');
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->setLocale('xyz');

        $peas = factory(Vegetable::class)->create(['name:en' => 'Peas']);

        static::assertNull($peas->translation);
    }

    /** @test */
    public function can_fill_conflicting_attribute_locale(): void
    {
        $this->app->make('config')->set('translatable.locales', ['en', 'id']);
        $this->app->make(Locales::class)->load();

        $country = new Country([
            'code' => 'my',
            'id' => [
                'name' => 'id:my country',
            ],
            'en' => [
                'name' => 'en:my country',
            ],
        ]);

        $country->fill([
            'id' => 100,
        ]);

        $country->save();

        static::assertEquals(100, $country->getKey());
        static::assertEquals('id:my country', $country->getTranslation('id', false)->name);
        static::assertEquals('en:my country', $country->getTranslation('en', false)->name);
    }

    /** @test */
    public function it_returns_first_existing_translation_as_fallback(): void
    {
        /** @var Locales $helper */
        $helper = $this->app->make(Locales::class);

        $this->app->make('config')->set('translatable.locales', [
            'xyz',
            'en',
            'de' => [
                'DE',
                'AT',
            ],
            'fr',
            'el',
        ]);
        $this->app->make('config')->set('translatable.fallback_locale', null);
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->setLocale('xyz');

        $helper->load();
        /** @var Country $country */
        $country = Country::create(['code' => 'gr']);
        CountryTranslation::create([
            'country_id' => $country->id,
            'locale' => 'en',
            'name' => 'Greece',
        ]);
        CountryTranslation::create([
            'country_id' => $country->id,
            'locale' => 'de',
            'name' => 'Griechenland',
        ]);
        CountryTranslation::create([
            'country_id' => $country->id,
            'locale' => $helper->getCountryLocale('de', 'DE'),
            'name' => 'Griechenland',
        ]);

        static::assertNull($country->getTranslation(null, false));

        // returns first existing locale
        $translation = $country->getTranslation();
        static::assertInstanceOf(CountryTranslation::class, $translation);
        static::assertEquals('en', $translation->locale);

        // still returns simple locale for country based locale
        $translation = $country->getTranslation($helper->getCountryLocale('de', 'AT'));
        static::assertInstanceOf(CountryTranslation::class, $translation);
        static::assertEquals('de', $translation->locale);

        $this->app->make('config')->set('translatable.locales', [
            'xyz',
            'de' => [
                'DE',
                'AT',
            ],
            'en',
            'fr',
            'el',
        ]);
        $helper->load();

        // returns simple locale before country based locale
        $translation = $country->getTranslation();
        static::assertInstanceOf(CountryTranslation::class, $translation);
        static::assertEquals('de', $translation->locale);

        $country->translations()->where('locale', 'de')->delete();
        $country->unsetRelation('translations');

        // returns country based locale before next simple one
        $translation = $country->getTranslation();
        static::assertInstanceOf(CountryTranslation::class, $translation);
        static::assertEquals($helper->getCountryLocale('de', 'DE'), $translation->locale);
    }

    /** @test */
    public function it_uses_translation_relation_if_locale_matches(): void
    {
        $this->app->make('config')->set('translatable.use_fallback', false);
        $this->app->setLocale('de');
        Country::create(['code' => 'gr', 'name:de' => 'Griechenland']);

        /** @var Country $country */
        $country = Country::first();
        $country->load('translation');

        static::assertTrue($country->relationLoaded('translation'));
        static::assertFalse($country->relationLoaded('translations'));

        $translation = $country->getTranslation();
        static::assertInstanceOf(CountryTranslation::class, $translation);
        static::assertEquals('de', $translation->locale);
        static::assertFalse($country->relationLoaded('translations'));
    }

    /** @test */
    public function it_uses_translations_relation_if_locale_does_not_match(): void
    {
        $this->app->make('config')->set('translatable.use_fallback', false);
        $this->app->setLocale('de');
        Country::create(['code' => 'gr', 'name:de' => 'Griechenland', 'name:en' => 'Greece']);

        /** @var Country $country */
        $country = Country::first();
        $country->load('translation');

        static::assertTrue($country->relationLoaded('translation'));
        static::assertFalse($country->relationLoaded('translations'));
        $this->app->setLocale('en');

        $translation = $country->getTranslation();
        static::assertInstanceOf(CountryTranslation::class, $translation);
        static::assertEquals('en', $translation->locale);
        static::assertTrue($country->relationLoaded('translations'));
    }

    /** @test */
    public function it_does_not_load_translation_relation_if_not_already_loaded(): void
    {
        $this->app->make('config')->set('translatable.use_fallback', false);
        $this->app->setLocale('de');
        Country::create(['code' => 'gr', 'name:de' => 'Griechenland', 'name:en' => 'Greece']);

        /** @var Country $country */
        $country = Country::first();
        static::assertFalse($country->relationLoaded('translation'));
        static::assertFalse($country->relationLoaded('translations'));

        $translation = $country->getTranslation();
        static::assertInstanceOf(CountryTranslation::class, $translation);
        static::assertEquals('de', $translation->locale);
        static::assertFalse($country->relationLoaded('translation'));
        static::assertTrue($country->relationLoaded('translations'));
    }

    /** @test */
    public function it_does_not_delete_translations_on_cascade_by_default()
    {
        $vegetable = factory(Vegetable::class)->create(['name:en' => 'Peas']);

        $this->assertDatabaseHas('vegetables', ['identity' => $vegetable->identity]);
        $this->assertDatabaseHas('vegetable_translations', ['vegetable_identity' => $vegetable->identity]);

        $vegetable->delete();

        $this->assertDatabaseMissing('vegetables', ['identity' => $vegetable->identity]);
        $this->assertDatabaseHas('vegetable_translations', ['vegetable_identity' => $vegetable->identity]);
    }

    /** @test */
    public function it_deletes_translations_on_cascade()
    {
        Vegetable::enableDeleteTranslationsCascade();
        $vegetable = factory(Vegetable::class)->create(['name:en' => 'Peas']);

        $this->assertDatabaseHas('vegetables', ['identity' => $vegetable->identity]);
        $this->assertDatabaseHas('vegetable_translations', ['vegetable_identity' => $vegetable->identity]);

        $vegetable->delete();

        $this->assertDatabaseMissing('vegetables', ['identity' => $vegetable->identity]);
        $this->assertDatabaseMissing('vegetable_translations', ['vegetable_identity' => $vegetable->identity]);
    }

    /** @test */
    public function it_does_not_delete_on_cascade_after_retrieving_a_model()
    {
        Vegetable::enableDeleteTranslationsCascade();
        $vegetable = factory(Vegetable::class)->create(['name:en' => 'Peas']);
        Vegetable::disableDeleteTranslationsCascade();

        $this->assertDatabaseHas('vegetables', ['identity' => $vegetable->identity]);
        $this->assertDatabaseHas('vegetable_translations', ['vegetable_identity' => $vegetable->identity]);

        $vegetable->delete();

        $this->assertDatabaseMissing('vegetables', ['identity' => $vegetable->identity]);
        $this->assertDatabaseHas('vegetable_translations', ['vegetable_identity' => $vegetable->identity]);
    }

    /** @test */
    public function it_can_restore_translations_in_a_transaction()
    {
        Vegetable::enableDeleteTranslationsCascade();
        $vegetable = factory(Vegetable::class)->create(['name:en' => 'Peas']);

        $this->assertDatabaseHas('vegetables', ['identity' => $vegetable->identity]);
        $this->assertDatabaseHas('vegetable_translations', ['vegetable_identity' => $vegetable->identity]);

        DB::connection()->beginTransaction();
        $vegetable->delete();
        DB::connection()->rollBack();

        $this->assertDatabaseHas('vegetables', ['identity' => $vegetable->identity]);
        $this->assertDatabaseHas('vegetable_translations', ['vegetable_identity' => $vegetable->identity]);
    }
}
