<?php

namespace Astrotomic\Translatable\Tests\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class StrictTranslation extends Eloquent
{
    public $timestamps = false;
    protected $table = 'country_translations';
}
