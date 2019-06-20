<?php

namespace Astrotomic\Translatable\Test\Model;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;

class CountryStrict extends Eloquent implements TranslatableContract
{
    use Translatable;

    /**
     * Array with the fields translated in the Translation table.
     *
     * @var array
     */
    public $translatedAttributes = ['name'];

    /**
     * Here we set a custom model for translation.
     * The convention would be Astrotomic\Translatable\Test\Model\CountryStrictTranslation.
     *
     * @var string Class containing the translation
     */
    public $translationModel = 'Astrotomic\Translatable\Test\Model\StrictTranslation';

    /**
     * @var string Foreign key for the translation relationship
     */
    public $translationForeignKey = 'country_id';

    /**
     * Column containing the locale in the translation table.
     * Defaults to 'locale'.
     *
     * @var string
     */
    public $localeKey;

    public $table = 'countries';

    /**
     * Add your translated attributes here if you want
     * fill them with mass assignment.
     *
     * @var array
     */
    public $fillable = ['code'];

    protected $softDelete = true;
}
