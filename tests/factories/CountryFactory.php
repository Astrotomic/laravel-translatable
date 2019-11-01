<?php

use Faker\Generator as Faker;
use Astrotomic\Translatable\Tests\Models\Country;
use Illuminate\Database\Eloquent\Factory as ModelFactory;

/* @var ModelFactory $factory */

$factory->define(Country::class, function (Faker $faker) {
    return [
        'code' => $faker->countryCode,
    ];
});
