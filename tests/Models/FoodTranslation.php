<?php

namespace Astrotomic\Translatable\Tests\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class FoodTranslation extends Eloquent
{
    public $timestamps = false;
    public $fillable = ['name'];
}
