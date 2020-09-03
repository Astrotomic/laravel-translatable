<?php

namespace Astrotomic\Translatable\Tests;

use Astrotomic\Translatable\Tests\Eloquent\Vegetable;
use Astrotomic\Translatable\Tests\Eloquent\VegetableTranslation;

final class TranslationTest extends TestCase
{
	/** @test */
    public function it_finds_the_default_translatable_class(): void
    {
        static::assertEquals(
            Vegetable::class,
            (new VegetableTranslation())->getTranslatableModelNameDefault()
        );
	}

	/** @test */
    public function it_finds_the_translatable_class_with_namespace_set(): void
    {
        $this->app->make('config')->set('translatable.translatable_model_namespace', 'App\Models\Translatables');

        static::assertEquals(
            'App\Models\Translatables\Vegetable',
            (new Vegetable())->getTranslatableModelNameDefault()
        );
    }
	
	/** @test */
	public function it_finds_the_translatable_class(): void
	{
		static::assertEquals(
			'Astrotomic\Translatable\Tests\Eloquent\Vegetable',
			(new VegetableTranslation())->getTranslatableModelName()
		);
	}

	/** @test */
    public function it_returns_custom_TranslatableModelName(): void
    {
        $vegetableTranslation = new VegetableTranslation();

        static::assertEquals(
            $vegetableTranslation->getTranslatableModelNameDefault(),
            $vegetableTranslation->getTranslatableModelName()
        );

        $vegetableTranslation->translatableModel = 'MyAwesomeVegetable';
        static::assertEquals(
            'MyAwesomeVegetable',
            $vegetableTranslation->getTranslatableModelName()
        );
    }
}
