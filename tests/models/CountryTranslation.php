<?php

namespace Astrotomic\Translatable\Test\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class CountryTranslation extends Eloquent
{
    public $timestamps = false;

    protected $fillable = [
        'country_id',
        'locale',
        'name',
    ];
}
