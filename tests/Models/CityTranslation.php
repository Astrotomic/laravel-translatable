<?php

namespace Astrotomic\Translatable\Tests\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class CityTranslation extends Eloquent
{
    public $timestamps = false;
    public $fillable = ['name'];
}
