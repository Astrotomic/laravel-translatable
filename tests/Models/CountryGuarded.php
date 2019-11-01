<?php

namespace Astrotomic\Translatable\Tests\Models;

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

    public $translationModel = CountryTranslation::class;
    public $translationForeignKey = 'country_id';
}
