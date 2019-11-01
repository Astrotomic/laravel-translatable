<?php

namespace Astrotomic\Translatable\Tests\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class CountryTranslationGuarded extends Eloquent
{
    public $timestamps = false;
    public $table = 'country_translations';

    protected $fillable = [];
}
