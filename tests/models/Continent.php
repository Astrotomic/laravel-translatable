<?php

namespace Astrotomic\Translatable\Test\Model;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;

/**
 * A test class that has no required properties.
 */
class Continent extends Eloquent implements TranslatableContract
{
    use Translatable;

    public $translatedAttributes = ['name'];
}
