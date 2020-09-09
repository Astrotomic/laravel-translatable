<?php

namespace Astrotomic\Translatable\Tests\Factories;

use Astrotomic\Translatable\Tests\Eloquent\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model
     *
     * @var string
     */
    protected $model = Country::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => $this->faker->countryCode,
        ];
    }
}
