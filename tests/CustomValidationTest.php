<?php

declare(strict_types=1);

namespace Tests;

use Astrotomic\Translatable\Locales;
use Astrotomic\Translatable\Validation\Rules\TranslatableExists;
use Astrotomic\Translatable\Validation\Rules\TranslatableUnique;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Eloquent\Person;
use Tests\Eloquent\Vegetable;

final class CustomValidationTest extends TestCase
{
    /**
     * Validate that the field is unique.
     *
     * @test
     */
    public function validate_field_unique(): void
    {
        $person = new Person(['name' => 'john doe']);
        $person->save();

        $data = [
            'name' => 'john andrew',
            'email' => 'john@example.com',
        ];

        $validator = Validator::make($data, [
            'name' => ['required', new TranslatableUnique(Person::class, 'name')],
        ]);

        self::assertFalse($validator->fails());
    }

    /**
     * Validate that the field is unique and fails.
     *
     * @test
     */
    public function validate_field_unique_fails(): void
    {
        $person = new Person(['name' => 'john doe']);
        $person->save();

        $data = [
            'name:en' => 'john doe',
            'email' => 'john@example.com',
        ];

        $this->expectException(ValidationException::class);

        Validator::make($data, [
            'name:en' => ['required', new TranslatableUnique(Person::class, 'name:en')],
        ])->validate();
    }

    /**
     * Validate that the field rule for unique fails.
     *
     * @test
     */
    public function validate_field_rule_unique_fails(): void
    {
        $person = new Person(['name' => 'john doe']);
        $person->save();

        $data = [
            'name:en' => 'john doe',
            'email' => 'john@example.com',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(Lang::get('translatable::validation.translatableUnique', ['attribute' => 'name:en']));

        Validator::make($data, [
            'name:en' => ['required', Rule::translatableUnique(Person::class, 'name:en')],
        ])->validate();

    }

    /**
     * Validate that the field rule for unique pass on update, ignoring model
     *
     * @test
     */
    public function validate_field_rule_unique_pass_on_update_ignore_model(): void
    {
        $vegetable = new Vegetable(['name' => 'Potatoes', 'quantity' => '5']);
        $vegetable->save();

        $data = [
            'name:en' => 'Potatoes',
            'quantity' => '3',
        ];

        Validator::make($data, [
            'name:en' => ['required', Rule::translatableUnique(Vegetable::class, 'name:en')->ignore($vegetable)],
        ])->validate();

        $this->assertTrue(true);

    }

    /**
     * Validate that the field rule for unique pass on update.
     *
     * @test
     */
    public function validate_field_rule_unique_pass_on_update_ignore_int(): void
    {
        $vegetable = new Vegetable(['name' => 'Potatoes', 'quantity' => '5']);
        $vegetable->save();

        $data = [
            'name:en' => 'Potatoes',
            'quantity' => '3',
        ];

        Validator::make($data, [
            'name:en' => ['required', Rule::translatableUnique(Vegetable::class, 'name:en')->ignore(1)],
        ])->validate();

        $this->assertTrue(true);

    }

    /**
     * Validate that the field exists.
     *
     * @test
     */
    public function validate_field_exists(): void
    {
        $person = new Person(['name' => 'john doe']);
        $person->save();

        $data = [
            'name' => 'john andrew',
            'email' => 'john@example.com',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(Lang::get('translatable::validation.translatableExist', ['attribute' => 'name']));

        Validator::make($data, [
            'name' => ['required', new TranslatableExists(Person::class, 'name')],
        ])->validate();
    }

    /**
     * Validate that the field exists and fails.
     *
     * @test
     */
    public function validate_field_exists_fails(): void
    {
        $person = new Person(['name' => 'john doe']);
        $person->save();

        $data = [
            'name' => 'john doe',
            'email' => 'john@example.com',
        ];

        $validator = Validator::make($data, [
            'name' => ['required', new TranslatableExists(Person::class, 'name')],
        ]);

        self::assertFalse($validator->fails());
    }

    /**
     * Validate that the field rule for exists fails.
     *
     * @test
     */
    public function validate_field_rule_exists_fails(): void
    {
        $person = new Person(['name' => 'john doe']);
        $person->save();

        $data = [
            'name' => 'john doe',
            'email' => 'john@example.com',
        ];

        $validator = Validator::make($data, [
            'name' => ['required', Rule::translatableExists(Person::class, 'name')],
        ]);

        self::assertFalse($validator->fails());
    }

    /**
     * Set up the test environment before each test.
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
