<?php

namespace Astrotomic\Translatable\Test\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class FoodTranslation extends Eloquent
{
    public $timestamps = false;
    public $fillable = ['name'];
}
