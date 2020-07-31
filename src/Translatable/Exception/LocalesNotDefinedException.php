<?php

namespace Astrotomic\Translatable\Exception;

class LocalesNotDefinedException extends \Exception
{
    public static function make(): self
    {
        return new static('Please make sure you have run `php artisan vendor:publish --provider="Astrotomic\Translatable\TranslatableServiceProvider"` and that the locales configuration is defined.');
    }
}
