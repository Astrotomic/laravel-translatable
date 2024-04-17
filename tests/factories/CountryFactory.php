<?php

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory as ModelFactory;
use Tests\Eloquent\Country;

/* @var ModelFactory $factory */

$factory->define(Country::class, function (Faker $faker) {
    return [
        'code' => $faker->countryCode,
    ];
});
