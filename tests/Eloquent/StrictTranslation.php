<?php

namespace Tests\Eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;

class StrictTranslation extends Eloquent
{
    public $timestamps = false;

    protected $table = 'country_translations';
}
