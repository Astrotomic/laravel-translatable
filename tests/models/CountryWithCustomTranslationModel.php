<?php

namespace Astrotomic\Translatable\Test\Model;

use Astrotomic\Translatable\Translatable;

class CountryWithCustomTranslationModel extends Country
{
    use Translatable;

    public $table = 'countries';
    public $translationForeignKey = 'country_id';
    public $translationModel = 'Astrotomic\Translatable\Test\Model\CountryTranslation';
}
