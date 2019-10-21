<?php

use Astrotomic\Translatable\Test\Model\Vegetable;

final class EloquentOverrideTest extends TestCase
{
    /** @test */
    public function to_array_returns_translated_attributes()
    {
        $vegetable = factory(Vegetable::class)->make(['name:en' => 'Peas']);

        static::assertArrayHasKey('name', $vegetable->toArray());
        static::assertEquals('Peas', $vegetable->toArray()['name']);
    }

    /** @test */
    public function to_array_wont_break_if_no_translations_exist()
    {
        $vegetable = factory(Vegetable::class)->make();

        static::assertIsArray($vegetable->toArray());
    }

    /** @test */
    public function translated_attributes_can_be_accessed_as_properties()
    {
        $vegetable = factory(Vegetable::class)->make(['name:en' => 'Peas']);

        static::assertTrue(isset($vegetable->name));
        static::assertEquals('Peas', $vegetable->name);
    }

    /** @test */
    public function it_can_hide_translated_attributes()
    {
        $vegetable = factory(Vegetable::class)->make(['name:en' => 'Peas']);

        static::assertTrue(isset($vegetable->toArray()['name']));

        $vegetable->setHidden(['name']);

        static::assertFalse(isset($vegetable->toArray()['name']));
    }

    /** @test */
    public function it_finds_custom_primary_keys()
    {
        $vegetable = new Vegetable();

        static::assertEquals('vegetable_identity', $vegetable->getTranslationRelationKey());
    }

    /** @test */
    public function setAttribute_returns_parent_setAttribute()
    {
        $vegetable = new Vegetable();

        static::assertSame($vegetable, $vegetable->setAttribute('name', 'China'));
    }
}
