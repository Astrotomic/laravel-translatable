<?php

use Astrotomic\Translatable\Test\Model\Vegetable;

final class EloquentOverrideTest extends TestCase
{
    
    /** @test */
    public function to_array_returns_translated_attributes()
    {
        $vegetable = factory(Vegetable::class)->make(['name:en' => 'Peas']);

        $this->assertArrayHasKey('name', $vegetable->toArray());
        $this->assertEquals('Peas', $vegetable->toArray()['name']);
    }

    /** @test */
    public function to_array_wont_break_if_no_translations_exist()
    {
        $vegetable = factory(Vegetable::class)->make();

        $this->assertIsArray($vegetable->toArray());
    }

    /** @test */
    public function translated_attributes_can_be_accessed_as_properties()
    {
        $vegetable = factory(Vegetable::class)->make(['name:en' => 'Peas']);

        $this->assertTrue(isset($vegetable->name));
        $this->assertEquals('Peas', $vegetable->name);
    }

    /** @test */
    public function it_can_hide_translated_attributes()
    {
        $vegetable = factory(Vegetable::class)->make(['name:en' => 'Peas']);

        $this->assertTrue(isset($vegetable->toArray()['name']));

        $vegetable->setHidden(['name']);

        $this->assertFalse(isset($vegetable->toArray()['name']));
    }

    /** @test */
    public function it_finds_custom_primary_keys()
    {
        $vegetable = new Vegetable();

        $this->assertEquals('vegetable_identity', $vegetable->getRelationKey());
    }

    /** @test */
    public function setAttribute_returns_parent_setAttribute()
    {
        $vegetable = new Vegetable();

        $this->assertSame($vegetable, $vegetable->setAttribute('name', 'China'));
    }

}
