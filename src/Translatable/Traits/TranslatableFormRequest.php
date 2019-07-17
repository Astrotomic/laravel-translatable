<?php

namespace Astrotomic\Translatable\Traits;

use Astrotomic\Translatable\Locales;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

trait TranslatableFormRequest
{
    /**
     * Create the default validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Factory  $factory
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function createDefaultValidator(ValidationFactory $factory)
    {
        if(!\method_exists($this, 'translatableRules'))
            return parent::createDefaultValidator($factory);

        $rules = array_merge(
            $this->container->call([$this, 'rules']),
            $this->makeRulesByLocales(
                $this->container->call([$this, 'translatableRules'])
            )
        );

        return $factory->make(
            $this->validationData(), $rules,
            $this->messages(), $this->attributes()
        );
    }

    private function makeRulesByLocales($rules)
    {
        $locales = app('translatable.locales')->all();

        $translatableRules = [];

        foreach($rules as $key => $value)
        {
            $translatableRules[$key] = 'required|array';
            foreach($locales as $locale) 
            {
                $translatableRules[$key.'.'.$locale] = $value;
            }
        }

        return $translatableRules;
    }
}