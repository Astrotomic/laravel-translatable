<?php

namespace Astrotomic\Translatable\Test\Model;

use Astrotomic\Translatable\Translatable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;

class CountryWithCustomTranslationModel extends Country implements TranslatableContract
{
    use Translatable;

    public $table = 'countries';
    public $translationForeignKey = 'country_id';
    public $translationModel = 'Astrotomic\Translatable\Test\Model\CountryTranslation';
}
