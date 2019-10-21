<?php

namespace Astrotomic\Translatable\Test\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class VegetableTranslation extends Eloquent
{
    public $timestamps = false;

    protected $fillable = [
        'vegetable_identity',
        'locale',
        'name',
    ];
}
