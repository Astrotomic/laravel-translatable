<?php

namespace Astrotomic\Translatable\Tests\Models;

use Astrotomic\Translatable\Translatable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;

class CountryWithCustomTranslationModel extends Country implements TranslatableContract
{
    use Translatable;

    public $table = 'countries';
    public $translationForeignKey = 'country_id';
    public $translationModel = CountryTranslation::class;
}
