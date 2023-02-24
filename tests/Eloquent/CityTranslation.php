<?php

declare(strict_types=1);

namespace Astrotomic\Translatable\Tests\Eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;

class CityTranslation extends Eloquent
{
    public $timestamps = false;

    protected $guarded = ['city_identity'];
}
