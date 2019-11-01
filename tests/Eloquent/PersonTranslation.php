<?php

namespace Astrotomic\Translatable\Tests\Eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;

class PersonTranslation extends Eloquent
{
    public $timestamps = false;
    public $fillable = ['name'];
}
