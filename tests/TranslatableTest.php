<?php

namespace Tests;

use Astrotomic\Translatable\Locales;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\Eloquent\Country;
use Tests\Eloquent\CountryStrict;
use Tests\Eloquent\CountryTranslation;
use Tests\Eloquent\Person;
use Tests\Eloquent\Vegetable;
use Tests\Eloquent\VegetableNumeric;
use Tests\Eloquent\VegetableTranslation;

final class TranslatableTest extends TestCase
{
    #[Test]
    public function it_finds_the_default_translation_class(): void
    {
        Assert::assertSame(
            VegetableTranslation::class,
            (new Vegetable)->getTranslationModelNameDefault()
        );
    }

    #[Test]
    public function it_finds_the_translation_class_with_namespace_set(): void
    {
        $this->app->make('config')->set('translatable.translation_model_namespace', 'App\Models\Translations');

        Assert::assertSame(
            'App\Models\Translations\VegetableTranslation',
            (new Vegetable)->getTranslationModelNameDefault()
        );
    }

    #[Test]
    public function it_finds_the_translation_class_with_suffix_set(): void
    {
        $this->app->make('config')->set('translatable.translation_suffix', 'Trans');

        Assert::assertSame(
            'Tests\Eloquent\VegetableTrans',
            (new Vegetable)->getTranslationModelName()
        );
    }

    #[Test]
    public function it_returns_custom_translation_model_name(): void
    {
        $vegetable = new Vegetable;

        Assert::assertSame(
            $vegetable->getTranslationModelNameDefault(),
            $vegetable->getTranslationModelName()
        );

        $vegetable->translationModel = 'MyAwesomeVegetableTranslation';
        Assert::assertSame(
            'MyAwesomeVegetableTranslation',
            $vegetable->getTranslationModelName()
        );
    }

    #[Test]
    public function it_returns_relation_key(): void
    {
        $vegetable = new Vegetable;
        Assert::assertSame('vegetable_identity', $vegetable->getTranslationRelationKey());

        $vegetable->translationForeignKey = 'my_awesome_key';
        Assert::assertSame('my_awesome_key', $vegetable->getTranslationRelationKey());
    }

    #[Test]
    public function it_returns_the_translation(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:el' => 'Αρακάς', 'name:en' => 'Peas']);

        Assert::assertSame('Αρακάς', $vegetable->translate('el')->name);

        Assert::assertSame('Peas', $vegetable->translate('en')->name);

        $this->app->setLocale('el');
        Assert::assertSame('Αρακάς', $vegetable->translate()->name);

        $this->app->setLocale('en');
        Assert::assertSame('Peas', $vegetable->translate()->name);
    }

    #[Test]
    public function it_returns_the_translation_with_accessor(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:el' => 'Αρακάς', 'name:en' => 'Peas']);

        Assert::assertSame('Αρακάς', $vegetable->{'name:el'});
        Assert::assertSame('Peas', $vegetable->{'name:en'});
    }

    #[Test]
    public function it_returns_null_when_the_locale_doesnt_exist(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:el' => 'Αρακάς']);

        Assert::assertNull($vegetable->{'name:unknown-locale'});
    }

    #[Test]
    public function it_saves_translations(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:el' => 'Αρακάς', 'name:en' => 'Peas']);

        Assert::assertSame('Peas', $vegetable->name);

        $vegetable->name = 'Pea';
        $vegetable->save();
        $vegetable->refresh();

        Assert::assertSame('Pea', $vegetable->name);
    }

    #[Test]
    public function it_saves_translations_with_mutator(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:el' => 'Αρακάς', 'name:en' => 'Peas']);

        $vegetable->{'name:en'} = 'Pea';
        $vegetable->{'name:el'} = 'Μπιζέλι';
        $vegetable->save();
        $vegetable->refresh();

        $this->app->setLocale('en');
        Assert::assertSame('Pea', $vegetable->translate()->name);

        $this->app->setLocale('el');
        Assert::assertSame('Μπιζέλι', $vegetable->translate()->name);
    }

    #[Test]
    public function it_does_not_lazy_load_translations_when_updating_non_translated_attributes(): void
    {
        DB::enableQueryLog();

        $vegetable = factory(Vegetable::class)->create();
        Assert::assertFalse($vegetable->relationLoaded('translations'));
        Assert::assertCount(1, DB::getQueryLog());

        DB::flushQueryLog();

        $vegetable->update(['quantity' => 5]);
        Assert::assertFalse($vegetable->relationLoaded('translations'));
        Assert::assertCount(1, DB::getQueryLog());

        DB::flushQueryLog();

        $vegetable->update(['name' => 'Germany']);
        Assert::assertTrue($vegetable->relationLoaded('translations'));
        Assert::assertCount(2, DB::getQueryLog());
        DB::disableQueryLog();
    }

    #[Test]
    public function it_uses_default_locale_to_return_translations(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:el' => 'Αρακάς']);

        $vegetable->translate('el')->name = 'Μπιζέλι';

        $this->app->setLocale('el');
        Assert::assertSame('Μπιζέλι', $vegetable->name);
        $vegetable->save();

        $vegetable->refresh();
        Assert::assertSame('Μπιζέλι', $vegetable->translate('el')->name);
    }

    #[Test]
    public function it_creates_translations_using_the_shortcut(): void
    {
        $vegetable = factory(Vegetable::class)->create();

        $vegetable->name = 'Peas';
        $vegetable->save();

        $vegetable = Vegetable::query()->first();
        Assert::assertSame('Peas', $vegetable->name);
        $this->assertDatabaseHas('vegetable_translations', [
            'vegetable_identity' => $vegetable->identity,
            'locale' => 'en',
            'name' => 'Peas',
        ]);
    }

    #[Test]
    public function it_creates_translations_using_mass_assignment(): void
    {
        $vegetable = Vegetable::query()->create([
            'quantity' => 5,
            'name' => 'Peas',
        ]);

        Assert::assertSame(5, $vegetable->quantity);
        Assert::assertSame('Peas', $vegetable->name);
    }

    #[Test]
    public function it_creates_translations_using_mass_assignment_and_locales(): void
    {
        $vegetable = Vegetable::query()->create([
            'quantity' => 5,
            'en' => ['name' => 'Peas'],
            'fr' => ['name' => 'Pois'],
        ]);

        Assert::assertSame(5, $vegetable->quantity);
        Assert::assertSame('Peas', $vegetable->translate('en')->name);
        Assert::assertSame('Pois', $vegetable->translate('fr')->name);

        $vegetable = Vegetable::query()->first();
        Assert::assertSame('Peas', $vegetable->translate('en')->name);
        Assert::assertSame('Pois', $vegetable->translate('fr')->name);
    }

    #[Test]
    public function it_creates_translations_using_wrapped_mass_assignment_and_locales(): void
    {
        $this->app->make('config')->set('translatable.translations_wrapper', '_translation_wrapper');

        $vegetable = Vegetable::query()->create([
            'quantity' => 5,
            '_translation_wrapper' => [
                'en' => ['name' => 'Peas'],
                'fr' => ['name' => 'Pois'],
            ],
        ]);

        Assert::assertSame(5, $vegetable->quantity);
        Assert::assertSame('Peas', $vegetable->translate('en')->name);
        Assert::assertSame('Pois', $vegetable->translate('fr')->name);

        $vegetable = Vegetable::query()->first();
        Assert::assertSame('Peas', $vegetable->translate('en')->name);
        Assert::assertSame('Pois', $vegetable->translate('fr')->name);
    }

    #[Test]
    public function it_creates_translations_using_wrapped_mass_assignment_and_locales_with_an_unguarded_model(): void
    {
        $this->app->make('config')->set('translatable.translations_wrapper', '_translation_wrapper');

        Vegetable::unguard();

        $vegetable = Vegetable::create([
            'quantity' => 5,
            '_translation_wrapper' => [
                'en' => ['name' => 'Peas'],
                'fr' => ['name' => 'Pois'],
            ],
        ]);

        Vegetable::reguard();

        Assert::assertSame(5, $vegetable->quantity);
        Assert::assertSame('Peas', $vegetable->translate('en')->name);
        Assert::assertSame('Pois', $vegetable->translate('fr')->name);

        $vegetable = Vegetable::first();
        Assert::assertSame('Peas', $vegetable->translate('en')->name);
        Assert::assertSame('Pois', $vegetable->translate('fr')->name);
    }

    #[Test]
    public function it_skips_mass_assignment_if_attributes_non_fillable(): void
    {
        $this->expectException(MassAssignmentException::class);
        $country = CountryStrict::query()->create([
            'code' => 'be',
            'en' => ['name' => 'Belgium'],
            'fr' => ['name' => 'Belgique'],
        ]);

        Assert::assertSame('be', $country->code);
        Assert::assertNull($country->translate('en'));
        Assert::assertNull($country->translate('fr'));
    }

    #[Test]
    public function it_returns_if_object_has_translation(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:en' => 'Peas']);

        Assert::assertTrue($vegetable->hasTranslation('en'));
        Assert::assertFalse($vegetable->hasTranslation('some-code'));
    }

    #[Test]
    public function it_returns_default_translation(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'de');

        $vegetable = factory(Vegetable::class)->create(['name:de' => 'Erbsen']);

        Assert::assertSame('Erbsen', $vegetable->getTranslation('ch', true)->name);
        Assert::assertSame('Erbsen', $vegetable->translateOrDefault('ch')->name);
        Assert::assertNull($vegetable->getTranslation('ch', false));

        $this->app->setLocale('ch');
        Assert::assertSame('Erbsen', $vegetable->translateOrDefault()->name);
    }

    #[Test]
    public function fallback_option_in_config_overrides_models_fallback_option(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'de');

        $vegetable = factory(Vegetable::class)->create(['name:de' => 'Erbsen']);
        Assert::assertSame('de', $vegetable->getTranslation('ch', true)->locale);

        $vegetable->useTranslationFallback = false;
        Assert::assertSame('de', $vegetable->getTranslation('ch', true)->locale);

        $vegetable->useTranslationFallback = true;
        Assert::assertSame('de', $vegetable->getTranslation('ch')->locale);

        $vegetable->useTranslationFallback = false;
        Assert::assertNull($vegetable->getTranslation('ch'));
    }

    #[Test]
    public function configuration_defines_if_fallback_is_used(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'de');
        $this->app->make('config')->set('translatable.use_fallback', true);

        $vegetable = factory(Vegetable::class)->create(['name:de' => 'Erbsen']);

        Assert::assertSame('de', $vegetable->getTranslation('ch')->locale);
    }

    #[Test]
    public function use_translation_fallback_overrides_configuration(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'de');
        $this->app->make('config')->set('translatable.use_fallback', true);

        $vegetable = factory(Vegetable::class)->create(['name:en' => 'Peas']);
        $vegetable->useTranslationFallback = false;

        Assert::assertNull($vegetable->getTranslation('ch'));
    }

    #[Test]
    public function it_returns_null_if_fallback_is_not_defined(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'ch');

        $vegetable = factory(Vegetable::class)->create(['name:en' => 'Peas']);

        Assert::assertNull($vegetable->getTranslation('pl', true));
    }

    #[Test]
    public function it_fills_a_non_default_language_with_fallback_set(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'en');

        $vegetable = new Vegetable;
        $vegetable->fill([
            'quantity' => 5,
            'en' => ['name' => 'Peas'],
            'de' => ['name' => 'Erbsen'],
        ]);

        Assert::assertSame('Peas', $vegetable->translate('en')->name);
    }

    #[Test]
    public function it_creates_a_new_translation(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'en');

        $vegetable = factory(Vegetable::class)->create();
        $vegetable->getNewTranslation('en')->name = 'Peas';
        $vegetable->save();

        Assert::assertSame('Peas', $vegetable->translate('en')->name);
    }

    #[Test]
    public function new_translation_can_be_saved_directly(): void
    {
        $vegetable = factory(Vegetable::class)->create();

        $translation = $vegetable->getNewTranslation('en');
        $translation->name = 'Peas';
        $translation->save();

        $this->assertDatabaseHas('vegetable_translations', [
            'locale' => 'en',
            'name' => 'Peas',
        ]);
    }

    #[Test]
    public function the_locale_key_is_locale_by_default(): void
    {
        $vegetable = new Vegetable;

        Assert::assertSame('locale', $vegetable->getLocaleKey());
    }

    #[Test]
    public function the_locale_key_can_be_overridden_in_configuration(): void
    {
        $this->app->make('config')->set('translatable.locale_key', 'language_id');

        $vegetable = new Vegetable;
        Assert::assertSame('language_id', $vegetable->getLocaleKey());
    }

    #[Test]
    public function the_locale_key_can_be_customized_per_model(): void
    {
        $vegetable = new Vegetable;
        $vegetable->localeKey = 'language_id';
        Assert::assertSame('language_id', $vegetable->getLocaleKey());
    }

    public function test_the_translation_model_can_be_customized(): void
    {
        CountryStrict::unguard();
        $country = CountryStrict::query()->create([
            'code' => 'es',
            'name:en' => 'Spain',
            'name:de' => 'Spanien',
        ]);
        Assert::assertTrue($country->exists);
        Assert::assertSame('Spain', $country->translate('en')->name);
        Assert::assertSame('Spanien', $country->translate('de')->name);
        CountryStrict::reguard();
    }

    #[Test]
    public function it_reads_the_configuration(): void
    {
        Assert::assertSame('Translation', $this->app->make('config')->get('translatable.translation_suffix'));
    }

    #[Test]
    public function getting_translation_does_not_create_translation(): void
    {
        $vegetable = factory(Vegetable::class)->create();

        Assert::assertNull($vegetable->getTranslation('en', false));
    }

    #[Test]
    public function getting_translated_field_does_not_create_translation(): void
    {
        $this->app->setLocale('en');
        $vegetable = factory(Vegetable::class)->create();

        Assert::assertNull($vegetable->getTranslation('en'));
    }

    #[Test]
    public function it_has_methods_that_return_always_a_translation(): void
    {
        $vegetable = factory(Vegetable::class)->create();
        Assert::assertSame('abc', $vegetable->translateOrNew('abc')->locale);

        $this->app->setLocale('xyz');
        Assert::assertSame('xyz', $vegetable->translateOrNew()->locale);
    }

    #[Test]
    public function it_throws_an_exception_if_translation_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage(sprintf('No query results for model [%s] %s', VegetableTranslation::class, 'xyz'));

        $vegetable = Vegetable::query()->create([
            'en' => ['name' => 'Peas'],
        ]);
        Assert::assertSame('en', $vegetable->translateOrFail('en')->locale);

        $vegetable->translateOrFail('xyz');
    }

    #[Test]
    public function it_returns_if_attribute_is_translated(): void
    {
        $vegetable = new Vegetable;

        Assert::assertTrue($vegetable->isTranslationAttribute('name'));
        Assert::assertFalse($vegetable->isTranslationAttribute('some-field'));
    }

    #[Test]
    public function config_overrides_apps_locale(): void
    {
        $veegtable = factory(Vegetable::class)->create(['name:de' => 'Erbsen']);
        App::make('config')->set('translatable.locale', 'de');

        Assert::assertSame('Erbsen', $veegtable->name);
    }

    #[Test]
    public function locales_as_array_keys_are_properly_detected(): void
    {
        $this->app->config->set('translatable.locales', ['en' => ['US', 'GB']]);

        $vegetable = Vegetable::query()->create([
            'en' => ['name' => 'Peas'],
            'en-US' => ['name' => 'US Peas'],
            'en-GB' => ['name' => 'GB Peas'],
        ]);

        Assert::assertSame('Peas', $vegetable->getTranslation('en')->name);
        Assert::assertSame('GB Peas', $vegetable->getTranslation('en-GB')->name);
        Assert::assertSame('US Peas', $vegetable->getTranslation('en-US')->name);
    }

    #[Test]
    public function locale_separator_can_be_configured(): void
    {
        $this->app->make('config')->set('translatable.locales', ['en' => ['GB']]);
        $this->app->make('config')->set('translatable.locale_separator', '_');
        $this->app->make('translatable.locales')->load();
        $vegetable = Vegetable::query()->create([
            'en_GB' => ['name' => 'Peas'],
        ]);

        Assert::assertSame('Peas', $vegetable->getTranslation('en_GB')->name);
    }

    #[Test]
    public function fallback_for_country_based_locales(): void
    {
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->make('config')->set('translatable.fallback_locale', 'fr');
        $this->app->make('config')->set('translatable.locales', ['en' => ['US', 'GB'], 'fr']);
        $this->app->make('config')->set('translatable.locale_separator', '-');
        $this->app->make('translatable.locales')->load();

        $vegetable = factory(Vegetable::class)->create([
            'fr' => ['name' => 'Frites'],
            'en-GB' => ['name' => 'Chips'],
            'en' => ['name' => 'French fries'],
        ]);

        Assert::assertSame('French fries', $vegetable->getTranslation('en-US')->name);
    }

    #[Test]
    public function fallback_for_country_based_locales_with_no_base_locale(): void
    {
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->make('config')->set('translatable.fallback_locale', 'en');
        $this->app->make('config')->set('translatable.locales', ['pt' => ['PT', 'BR'], 'en']);
        $this->app->make('config')->set('translatable.locale_separator', '-');
        $this->app->make('translatable.locales')->load();

        $vegetable = factory(Vegetable::class)->create([
            'en' => ['name' => 'Chips'],
            'pt-PT' => ['name' => 'Batatas fritas'],
        ]);

        Assert::assertSame('Chips', $vegetable->getTranslation('pt-BR')->name);
    }

    #[Test]
    public function to_array_and_fallback_with_country_based_locales_enabled(): void
    {
        $this->app->make('config')->set('translatable.locale', 'en-GB');
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->make('config')->set('translatable.fallback_locale', 'fr');
        $this->app->make('config')->set('translatable.locales', ['en' => ['GB'], 'fr']);
        $this->app->make('config')->set('translatable.locale_separator', '-');
        $this->app->make('translatable.locales')->load();

        $vegetable = factory(Vegetable::class)->create(['name:fr' => 'Frites']);

        Assert::assertSame('Frites', $vegetable['name']);
    }

    #[Test]
    public function it_skips_translations_in_to_array_when_config_is_set(): void
    {
        $this->app->make('config')->set('translatable.to_array_always_loads_translations', false);

        factory(Vegetable::class)->create(['name' => 'Peas']);

        $vegetable = Vegetable::query()->first()->toArray();
        Assert::assertFalse(isset($vegetable['name']));
    }

    #[Test]
    public function it_returns_translations_in_to_array_when_config_is_set_but_translations_are_loaded(): void
    {
        $this->app->make('config')->set('translatable.to_array_always_loads_translations', false);
        factory(Vegetable::class)->create(['name' => 'Peas']);

        $vegetable = Vegetable::with('translations')->first()->toArray();

        Assert::assertTrue(isset($vegetable['name']));
    }

    #[Test]
    public function it_should_mutate_the_translated_attribute_if_a_mutator_is_set_on_model(): void
    {
        $person = new Person(['name' => 'john doe']);
        $person->save();
        $person = Person::query()->find(1);
        Assert::assertSame('John Doe', $person->name);
    }

    #[Test]
    public function it_deletes_all_translations(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:es' => 'Guisantes', 'name:en' => 'Peas']);

        Assert::assertCount(2, $vegetable->translations);

        $vegetable->deleteTranslations();

        Assert::assertCount(0, $vegetable->translations);
    }

    #[Test]
    public function it_deletes_translations_for_given_locales(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:es' => 'Guisantes', 'name:en' => 'Peas']);

        Assert::assertCount(2, $vegetable->translations);

        $vegetable->deleteTranslations('es');

        Assert::assertCount(1, $vegetable->translations);
    }

    #[Test]
    public function passing_an_empty_array_should_not_delete_translations(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:es' => 'Guisantes', 'name:en' => 'Peas']);

        Assert::assertCount(2, $vegetable->translations);

        $vegetable->deleteTranslations([]);

        Assert::assertCount(2, $vegetable->translations);
    }

    #[Test]
    public function fill_with_translation_key(): void
    {
        $vegetable = new Vegetable;
        $vegetable->fill([
            'name:en' => 'Peas',
            'name:de' => 'Erbsen',
        ]);
        Assert::assertSame('Peas', $vegetable->translate('en')->name);
        Assert::assertSame('Erbsen', $vegetable->translate('de')->name);

        $vegetable->save();
        $vegetable = Vegetable::query()->first();
        Assert::assertSame('Peas', $vegetable->translate('en')->name);
        Assert::assertSame('Erbsen', $vegetable->translate('de')->name);
    }

    #[Test]
    public function it_uses_the_default_locale_from_the_model(): void
    {
        $vegetable = new Vegetable;
        $vegetable->fill([
            'name:en' => 'Peas',
            'name:fr' => 'Pois',
        ]);
        Assert::assertSame('Peas', $vegetable->name);
        $vegetable->setDefaultLocale('fr');
        Assert::assertSame('Pois', $vegetable->name);

        $vegetable->setDefaultLocale(null);

        $vegetable->save();
        $vegetable = Vegetable::query()->first();

        Assert::assertSame('Peas', $vegetable->name);
        $vegetable->setDefaultLocale('fr');
        Assert::assertSame('Pois', $vegetable->name);
    }

    #[Test]
    public function replicate_entity(): void
    {
        $vegetable = new Vegetable;
        $vegetable->fill([
            'name:fr' => 'Pomme',
            'name:en' => 'Apple',
            'name:de' => 'Apfel',
        ]);
        $vegetable->save();

        $replicated = $vegetable->replicateWithTranslations();
        $replicated->save();

        Assert::assertNotNull($replicated->identity);
        Assert::assertNotEquals($replicated->identity, $vegetable->identity);
        Assert::assertSame($replicated->translate('fr')->name, $vegetable->translate('fr')->name);
        Assert::assertSame($replicated->translate('en')->name, $vegetable->translate('en')->name);
        Assert::assertSame($replicated->translate('de')->name, $vegetable->translate('de')->name);

        Assert::assertNotNull($replicated->translate('fr')->vegetable_identity);
        Assert::assertNotEquals($replicated->translate('fr')->vegetable_identity, $vegetable->identity);
        Assert::assertSame($replicated->translate('fr')->vegetable_identity, $replicated->identity);
        Assert::assertNotEquals($replicated->translate('en')->vegetable_identity, $vegetable->identity);
        Assert::assertSame($replicated->translate('en')->vegetable_identity, $replicated->identity);
        Assert::assertNotEquals($replicated->translate('de')->vegetable_identity, $vegetable->identity);
        Assert::assertSame($replicated->translate('de')->vegetable_identity, $replicated->identity);
    }

    #[Test]
    public function can_get_translations_as_array(): void
    {
        $vegetable = factory(Vegetable::class)->create([
            'name:en' => 'Peas',
            'name:fr' => 'Pois',
            'name:de' => 'Erbsen',
        ]);

        Assert::assertEquals([
            'de' => ['name' => 'Erbsen'],
            'en' => ['name' => 'Peas'],
            'fr' => ['name' => 'Pois'],
        ], $vegetable->getTranslationsArray());
    }

    #[Test]
    public function fill_will_ignore_unknown_locales(): void
    {
        config(['translatable.locales' => ['en']]);

        $vegetable = new Vegetable;
        $vegetable->fill([
            'en' => ['name' => 'Peas'],
            'ua' => ['name' => 'unknown'],
        ]);
        $vegetable->save();

        $this->assertDatabaseHas('vegetable_translations', [
            'locale' => 'en',
            'name' => 'Peas',
        ]);

        $this->assertDatabaseMissing('vegetable_translations', ['locale' => 'ua']);
    }

    #[Test]
    public function fill_will_ignore_unknown_locales_with_translations(): void
    {
        config(['translatable.locales' => ['en']]);

        $vegetable = new Vegetable;
        $vegetable->fill([
            'name:en' => 'Peas',
            'name:ua' => 'unknown',
        ]);

        $vegetable->save();

        $this->assertDatabaseHas('vegetable_translations', [
            'locale' => 'en',
            'name' => 'Peas',
        ]);

        $this->assertDatabaseMissing('vegetable_translations', ['locale' => 'ua']);
    }

    #[Test]
    public function it_uses_fallback_locale_if_default_is_empty(): void
    {
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->make('config')->set('translatable.use_property_fallback', true);
        $this->app->make('config')->set('translatable.fallback_locale', 'en');
        $vegetable = new Vegetable;
        $vegetable->fill([
            'name:en' => 'Peas',
            'name:fr' => '',
        ]);

        $this->app->setLocale('en');
        Assert::assertSame('Peas', $vegetable->name);
        $this->app->setLocale('fr');
        Assert::assertSame('Peas', $vegetable->name);
    }

    #[Test]
    public function it_uses_value_when_fallback_is_not_available(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'it');
        $this->app->make('config')->set('translatable.use_fallback', true);

        $vegetable = new Vegetable;
        $vegetable->fill([
            'en' => ['name' => ''],
            'de' => ['name' => 'Erbsen'],
        ]);

        // verify translated attributed is correctly returned when empty (non-existing fallback is ignored)
        $this->app->setLocale('en');
        Assert::assertNull($vegetable->getAttribute('name'));

        $this->app->setLocale('de');
        Assert::assertSame('Erbsen', $vegetable->getAttribute('name'));
    }

    #[Test]
    public function empty_translated_attribute(): void
    {
        $this->app->setLocale('invalid');
        $vegetable = factory(Vegetable::class)->create();

        Assert::assertNull($vegetable->name);
    }

    #[Test]
    public function empty_translations_are_not_saved(): void
    {
        $vegetable = new Vegetable;
        $vegetable->fill([
            'en' => [],
            'de' => ['name' => 'Erbsen'],
        ]);

        $vegetable->save();

        $this->assertDatabaseHas('vegetable_translations', [
            'locale' => 'de',
            'name' => 'Erbsen',
        ]);

        $this->assertDatabaseMissing('vegetable_translations', ['locale' => 'en']);
    }

    #[Test]
    public function numeric_translated_attribute(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'de');
        $this->app->make('config')->set('translatable.use_fallback', true);

        $vegetable = new VegetableNumeric;

        $vegetable->fill([
            'en' => ['name' => '0'],
            'de' => ['name' => '1'],
            'fr' => ['name' => null],
        ]);
        $vegetable->save();

        $this->app->setLocale('en');
        Assert::assertSame('0', $vegetable->name);

        $this->app->setLocale('fr');
        Assert::assertSame('1', $vegetable->name);
    }

    #[Test]
    public function translation_relation(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'fr');
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->setLocale('en');

        $peas = factory(Vegetable::class)->create([
            'name:en' => 'Peas',
            'name:fr' => 'Pois',
        ]);

        Assert::assertInstanceOf(VegetableTranslation::class, $peas->translation);
        Assert::assertSame('en', $peas->translation->locale);
    }

    #[Test]
    public function translation_relation_can_use_fallback_locale(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'fr');
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->setLocale('en');

        $peas = factory(Vegetable::class)->create(['name:fr' => 'Pois']);

        Assert::assertInstanceOf(VegetableTranslation::class, $peas->translation);
        Assert::assertSame('fr', $peas->translation->locale);
    }

    #[Test]
    public function translation_relation_returns_null_if_no_available_locale_was_found(): void
    {
        $this->app->make('config')->set('translatable.fallback_locale', 'xyz');
        $this->app->make('config')->set('translatable.use_fallback', true);
        $this->app->setLocale('xyz');

        $peas = factory(Vegetable::class)->create(['name:en' => 'Peas']);

        Assert::assertNull($peas->translation);
    }

    #[Test]
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

        Assert::assertSame(100, $country->getKey());
        Assert::assertSame('id:my country', $country->getTranslation('id', false)->name);
        Assert::assertSame('en:my country', $country->getTranslation('en', false)->name);
    }

    #[Test]
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
        $country = Country::query()->create(['code' => 'gr']);
        CountryTranslation::query()->create([
            'country_id' => $country->id,
            'locale' => 'en',
            'name' => 'Greece',
        ]);
        CountryTranslation::query()->create([
            'country_id' => $country->id,
            'locale' => 'de',
            'name' => 'Griechenland',
        ]);
        CountryTranslation::query()->create([
            'country_id' => $country->id,
            'locale' => $helper->getCountryLocale('de', 'DE'),
            'name' => 'Griechenland',
        ]);

        Assert::assertNull($country->getTranslation(null, false));

        // returns first existing locale
        $translation = $country->getTranslation();
        Assert::assertInstanceOf(CountryTranslation::class, $translation);
        Assert::assertSame('en', $translation->locale);

        // still returns simple locale for country based locale
        $translation = $country->getTranslation($helper->getCountryLocale('de', 'AT'));
        Assert::assertInstanceOf(CountryTranslation::class, $translation);
        Assert::assertSame('de', $translation->locale);

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
        Assert::assertInstanceOf(CountryTranslation::class, $translation);
        Assert::assertSame('de', $translation->locale);

        $country->translations()->where('locale', 'de')->delete();
        $country->unsetRelation('translations');

        // returns country based locale before next simple one
        $translation = $country->getTranslation();
        Assert::assertInstanceOf(CountryTranslation::class, $translation);
        Assert::assertSame($helper->getCountryLocale('de', 'DE'), $translation->locale);
    }

    #[Test]
    public function it_uses_translation_relation_if_locale_matches(): void
    {
        $this->app->make('config')->set('translatable.use_fallback', false);
        $this->app->setLocale('de');
        Country::query()->create(['code' => 'gr', 'name:de' => 'Griechenland']);

        /** @var Country $country */
        $country = Country::query()->first();
        $country->load('translation');

        Assert::assertTrue($country->relationLoaded('translation'));
        Assert::assertFalse($country->relationLoaded('translations'));

        $translation = $country->getTranslation();
        Assert::assertInstanceOf(CountryTranslation::class, $translation);
        Assert::assertSame('de', $translation->locale);
        Assert::assertFalse($country->relationLoaded('translations'));
    }

    #[Test]
    public function it_uses_translations_relation_if_locale_does_not_match(): void
    {
        $this->app->make('config')->set('translatable.use_fallback', false);
        $this->app->setLocale('de');
        Country::query()->create(['code' => 'gr', 'name:de' => 'Griechenland', 'name:en' => 'Greece']);

        /** @var Country $country */
        $country = Country::query()->first();
        $country->load('translation');

        Assert::assertTrue($country->relationLoaded('translation'));
        Assert::assertFalse($country->relationLoaded('translations'));
        $this->app->setLocale('en');

        $translation = $country->getTranslation();
        Assert::assertInstanceOf(CountryTranslation::class, $translation);
        Assert::assertSame('en', $translation->locale);
        Assert::assertTrue($country->relationLoaded('translations'));
    }

    #[Test]
    public function it_does_not_load_translation_relation_if_not_already_loaded(): void
    {
        $this->app->make('config')->set('translatable.use_fallback', false);
        $this->app->setLocale('de');
        Country::query()->create(['code' => 'gr', 'name:de' => 'Griechenland', 'name:en' => 'Greece']);

        /** @var Country $country */
        $country = Country::query()->first();
        Assert::assertFalse($country->relationLoaded('translation'));
        Assert::assertFalse($country->relationLoaded('translations'));

        $translation = $country->getTranslation();
        Assert::assertInstanceOf(CountryTranslation::class, $translation);
        Assert::assertSame('de', $translation->locale);
        Assert::assertFalse($country->relationLoaded('translation'));
        Assert::assertTrue($country->relationLoaded('translations'));
    }

    #[Test]
    public function it_does_not_delete_translations_on_cascade_by_default(): void
    {
        $vegetable = factory(Vegetable::class)->create(['name:en' => 'Peas']);

        $this->assertDatabaseHas('vegetables', ['identity' => $vegetable->identity]);
        $this->assertDatabaseHas('vegetable_translations', ['vegetable_identity' => $vegetable->identity]);

        $vegetable->delete();

        $this->assertDatabaseMissing('vegetables', ['identity' => $vegetable->identity]);
        $this->assertDatabaseHas('vegetable_translations', ['vegetable_identity' => $vegetable->identity]);
    }

    #[Test]
    public function it_deletes_translations_on_cascade(): void
    {
        Vegetable::enableDeleteTranslationsCascade();
        $vegetable = factory(Vegetable::class)->create(['name:en' => 'Peas']);

        $this->assertDatabaseHas('vegetables', ['identity' => $vegetable->identity]);
        $this->assertDatabaseHas('vegetable_translations', ['vegetable_identity' => $vegetable->identity]);

        $vegetable->delete();

        $this->assertDatabaseMissing('vegetables', ['identity' => $vegetable->identity]);
        $this->assertDatabaseMissing('vegetable_translations', ['vegetable_identity' => $vegetable->identity]);
    }

    #[Test]
    public function it_does_not_delete_on_cascade_after_retrieving_a_model(): void
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

    #[Test]
    public function it_can_restore_translations_in_a_transaction(): void
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
