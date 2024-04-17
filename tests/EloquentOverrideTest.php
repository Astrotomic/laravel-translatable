<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;
use Tests\Eloquent\Vegetable;

final class EloquentOverrideTest extends TestCase
{
    #[Test]
    public function to_array_returns_translated_attributes(): void
    {
        $vegetable = factory(Vegetable::class)->make(['name:en' => 'Peas']);

        self::assertArrayHasKey('name', $vegetable->toArray());
        self::assertEquals('Peas', $vegetable->toArray()['name']);
    }

    #[Test]
    public function to_array_wont_break_if_no_translations_exist(): void
    {
        $vegetable = factory(Vegetable::class)->make();

        self::assertIsArray($vegetable->toArray());
    }

    #[Test]
    public function translated_attributes_can_be_accessed_as_properties(): void
    {
        $vegetable = factory(Vegetable::class)->make(['name:en' => 'Peas']);

        self::assertTrue(isset($vegetable->name));
        self::assertEquals('Peas', $vegetable->name);
    }

    #[Test]
    public function it_can_hide_translated_attributes(): void
    {
        $vegetable = factory(Vegetable::class)->make(['name:en' => 'Peas']);

        self::assertTrue(isset($vegetable->toArray()['name']));

        $vegetable->setHidden(['name']);

        self::assertFalse(isset($vegetable->toArray()['name']));
    }

    #[Test]
    public function it_finds_custom_primary_keys(): void
    {
        $vegetable = new Vegetable();

        self::assertEquals('vegetable_identity', $vegetable->getTranslationRelationKey());
    }

    #[Test]
    public function setAttribute_returns_parent_setAttribute(): void
    {
        $vegetable = new Vegetable();

        self::assertSame($vegetable, $vegetable->setAttribute('name', 'China'));
    }
}
