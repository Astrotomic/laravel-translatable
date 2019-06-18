<?php

namespace Astrotomic\Translatable\Test\Model;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model as Eloquent;

class CountryGuarded extends Eloquent
{
    use Translatable;

    public $table = 'countries';
    protected $fillable = [];
    protected $guarded = ['*'];

    public $translatedAttributes = ['name'];

    public $translationModel = 'Astrotomic\Translatable\Test\Model\CountryTranslation';
    public $translationForeignKey = 'country_id';
}
