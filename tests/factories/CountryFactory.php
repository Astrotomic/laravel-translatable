<?php

use Faker\Generator as Faker;
use Astrotomic\Translatable\Test\Model\Country;

$factory->define(Country::class, function (Faker $faker) {
    return [
        'code' => $faker->countryCode,
    ];
});
