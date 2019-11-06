<?php

use Astrotomic\Translatable\Tests\Eloquent\Country;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory as ModelFactory;

/* @var ModelFactory $factory */

$factory->define(Country::class, function (Faker $faker) {
    return [
        'code' => $faker->countryCode,
    ];
});
