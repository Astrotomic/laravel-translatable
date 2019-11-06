<?php

namespace Astrotomic\Translatable\Tests\Eloquent;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Vegetable extends Eloquent implements TranslatableContract
{
    use Translatable;

    protected $primaryKey = 'identity';

    public $translationForeignKey = 'vegetable_identity';

    public $translatedAttributes = ['name'];

    protected $fillable = ['quantity'];

    public $localeKey;

    public $translationModel;
}
