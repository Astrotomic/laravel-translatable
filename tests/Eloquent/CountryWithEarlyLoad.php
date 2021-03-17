<?php

namespace Astrotomic\Translatable\Tests\Eloquent;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model as Eloquent;

class CountryWithEarlyLoad extends Eloquent implements TranslatableContract
{
    use Translatable;

    protected $table = 'countries';

    /**
     * Array with the fields translated in the Translation table.
     *
     * @var array
     */
    public $translatedAttributes = ['name'];

    protected $translationForeignKey = 'country_id';

    /**
     * Set $translationModel if you want to overwrite the convention
     * for the name of the translation Model. Use full namespace if applied.
     *
     * The convention is to add "Translation" to the name of the class extending Translatable.
     * Example: Country => CountryTranslation
     */
    public $translationModel = CountryTranslation::class;

    /**
     * Add your translated attributes here if you want
     * fill them with mass assignment.
     *
     * @var array
     */
    public $fillable = [];
    public $guarded = [];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translation'];
}
