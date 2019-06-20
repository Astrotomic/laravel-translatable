<?php

namespace Astrotomic\Translatable\Test\Model;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;

class CountryWithCustomLocaleKey extends Eloquent implements TranslatableContract
{
    use Translatable;

    public $table = 'countries';
    public $translatedAttributes = ['name'];

    /*
     * You can customize per model, which attribute will
     * be used to save the locale info into the database
     */
    public $localeKey = 'language_id';
}
