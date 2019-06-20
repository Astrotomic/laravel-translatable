<?php

namespace Astrotomic\Translatable\Test\Model;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;

class Vegetable extends Eloquent implements TranslatableContract
{
    use Translatable;

    protected $primaryKey = 'identity';

    protected $translationForeignKey = 'vegetable_identity';

    public $translatedAttributes = ['name'];
}
