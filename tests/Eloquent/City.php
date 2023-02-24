<?php

declare(strict_types=1);

namespace Astrotomic\Translatable\Tests\Eloquent;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model as Eloquent;

class City extends Eloquent implements TranslatableContract
{
    use Translatable;

    protected $guarded = ['id'];

    public $translatedAttributes = ['name'];

    public function getNameAttribute()
    {
        $locale = 'En';
        $this->locale = $locale;

        return $this->translate(mb_strtolower($locale))->name;
    }
}
