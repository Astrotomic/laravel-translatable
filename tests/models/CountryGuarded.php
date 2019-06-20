<?php

namespace Astrotomic\Translatable\Test\Model;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;

class CountryGuarded extends Eloquent implements TranslatableContract
{
    use Translatable;

    public $table = 'countries';
    protected $fillable = [];
    protected $guarded = ['*'];

    public $translatedAttributes = ['name'];

    public $translationModel = 'Astrotomic\Translatable\Test\Model\CountryTranslation';
    public $translationForeignKey = 'country_id';
}
