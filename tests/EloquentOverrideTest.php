<?php

use Astrotomic\Translatable\Test\Model\Vegetable;

final class EloquentOverrideTest extends TestCase
{
    /** @test */
    public function to_array_returns_translated_attributes(): void
    {
        $vegetable = factory(Vegetable::class)->make(['name:en' => 'Peas']);

        $this->assertArrayHasKey('name', $vegetable->toArray());
        $this->assertEquals('Peas', $vegetable->toArray()['name']);
    }

    /** @test */
    public function to_array_wont_break_if_no_translations_exist(): void
    {
        $vegetable = factory(Vegetable::class)->make();

        $this->assertIsArray($vegetable->toArray());
    }

    /** @test */
    public function translated_attributes_can_be_accessed_as_properties(): void
    {
        $vegetable = factory(Vegetable::class)->make(['name:en' => 'Peas']);

        $this->assertTrue(isset($vegetable->name));
        $this->assertEquals('Peas', $vegetable->name);
    }

    /** @test */
    public function it_can_hide_translated_attributes(): void
    {
        $vegetable = factory(Vegetable::class)->make(['name:en' => 'Peas']);

        $this->assertTrue(isset($vegetable->toArray()['name']));

        $vegetable->setHidden(['name']);

        $this->assertFalse(isset($vegetable->toArray()['name']));
    }

    /** @test */
    public function it_finds_custom_primary_keys(): void
    {
        $vegetable = new Vegetable();

        $this->assertEquals('vegetable_identity', $vegetable->getTranslationRelationKey());
    }

    /** @test */
    public function setAttribute_returns_parent_setAttribute(): void
    {
        $vegetable = new Vegetable();

        $this->assertSame($vegetable, $vegetable->setAttribute('name', 'China'));
    }
}
