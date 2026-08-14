<?php

namespace Tests\Eloquent;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model as Eloquent;

final class TranslatableModelWithoutConfiguration extends Eloquent implements TranslatableContract
{
    use Translatable;

    public $translatedAttributes = ['name'];
}
