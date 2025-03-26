<?php

namespace Tests\Eloquent;

class VegetableNumeric extends Vegetable
{
    public $translationModel = VegetableTranslation::class;

    protected $table = 'vegetables';

    protected function isEmptyTranslatableAttribute(string $key, $value): bool
    {
        if ($key === 'name') {
            return is_null($value);
        }

        return parent::isEmptyTranslatableAttribute($key, $value);
    }
}
