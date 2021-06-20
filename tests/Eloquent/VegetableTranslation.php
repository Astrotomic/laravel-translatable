<?php

namespace Astrotomic\Translatable\Tests\Eloquent;

use Astrotomic\Translation\Translation;
use Illuminate\Database\Eloquent\Model as Eloquent;

class VegetableTranslation extends Eloquent
{
    use Translation;

    public $timestamps = false;

    protected $fillable = [
        'vegetable_identity',
        'locale',
        'name',
    ];
}
