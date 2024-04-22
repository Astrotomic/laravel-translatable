<?php

declare(strict_types=1);

namespace Tests;

use Astrotomic\Translatable\Locales;
use Astrotomic\Translatable\Validation\Rules\TranslatableExists;
use Astrotomic\Translatable\Validation\Rules\TranslatableUnique;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PHPUnit\Framework\Attributes\Test;
use Tests\Eloquent\Person;

final class CustomValidationTest extends TestCase
{
    /**
     * Validate that the field is unique.
     *
     * @test
     * @return void
     */
    public function validate_field_unique(): void
    {
        $person = new Person(['name' => 'john doe']);
        $person->save();

        $data = [
            'name' => 'john andrew',
            'email' => 'john@example.com'
        ];

        $validator = Validator::make($data, [
            'name' => ['required', new TranslatableUnique(Person::class, 'name')],
        ]);

        if ($validator->fails()) {
            self::assertTrue(false);
        } else {
            self::assertTrue(true);
        }
    }

    /**
     * Validate that the field is unique and fails.
     *
     * @test
     * @return void
     */
    public function validate_field_unique_fails(): void
    {
        $person = new Person(['name' => 'john doe']);
        $person->save();

        $data = [
            'name:en' => 'john doe',
            'email' => 'john@example.com'
        ];

        $validator = Validator::make($data, [
            'name:en' => ['required', new TranslatableUnique(Person::class, 'name:en')],
        ]);

        if ($validator->fails() && $validator->errors()->first() === Lang::get('translatable::validation.translatableUnique', ['attribute' => 'name:en'])) {
            self::assertTrue(true);
        } else {
            self::assertTrue(false);
        }
    }

    /**
     * Validate that the field rule for unique fails.
     *
     * @test
     * @return void
     */
    public function validate_field_rule_unique_fails(): void
    {
        $person = new Person(['name' => 'john doe']);
        $person->save();

        $data = [
            'name:en' => 'john doe',
            'email' => 'john@example.com'
        ];

        $validator = Validator::make($data, [
            'name:en' => ['required', Rule::translatableUnique(Person::class, 'name:en')],
        ]);

        if ($validator->fails() && $validator->errors()->first() === Lang::get('translatable::validation.translatableUnique', ['attribute' => 'name:en'])) {
            self::assertTrue(true);
        } else {
            self::assertTrue(false);
        }
    }

    /**
     * Validate that the field exists.
     *
     * @test
     * @return void
     */
    public function validate_field_exists(): void
    {
        $person = new Person(['name' => 'john doe']);
        $person->save();

        $data = [
            'name' => 'john andrew',
            'email' => 'john@example.com'
        ];

        $validator = Validator::make($data, [
            'name' => ['required', new TranslatableExists(Person::class, 'name')],
        ]);

        if ($validator->fails()) {
            self::assertTrue(true);
        } else {
            self::assertTrue(false);
        }
    }

    /**
     * Validate that the field exists and fails.
     *
     * @test
     * @return void
     */
    public function validate_field_exists_fails(): void
    {
        $person = new Person(['name' => 'john doe']);
        $person->save();

        $data = [
            'name' => 'john doe',
            'email' => 'john@example.com'
        ];

        $validator = Validator::make($data, [
            'name' => ['required', new TranslatableExists(Person::class, 'name')],
        ]);

        if ($validator->fails() && $validator->errors()->first() === Lang::get('translatable::validation.TranslatableExists', ['attribute' => 'name'])) {
            self::assertTrue(false);
        } else {
            self::assertTrue(true);
        }
    }

    /**
     * Validate that the field rule for exists fails.
     *
     * @test
     * @return void
     */
    public function validate_field_rule_exists_fails(): void
    {
        $person = new Person(['name' => 'john doe']);
        $person->save();

        $data = [
            'name' => 'john doe',
            'email' => 'john@example.com'
        ];

        $validator = Validator::make($data, [
            'name' => ['required', Rule::translatableExists(Person::class, 'name')],
        ]);

        if ($validator->fails() && $validator->errors()->first() === Lang::get('translatable::validation.TranslatableExists', ['attribute' => 'name'])) {
            self::assertTrue(false);
        } else {
            self::assertTrue(true);
        }
    }

    /**
     * Set up the test environment before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        app('config')->set('translatable.locales', [
            'en',
            'de' => [
                'DE',
                'AT',
            ],
        ]);

        app(Locales::class)->load();
    }
}
