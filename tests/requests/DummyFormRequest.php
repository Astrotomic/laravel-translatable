<?php

namespace Astrotomic\Translatable\Test\Request;

use Illuminate\Foundation\Http\FormRequest;
use Astrotomic\Translatable\Traits\TranslatableFormRequest;

class DummyFormRequest extends FormRequest
{
    use TranslatableFormRequest;
    
    public function rules()
    {
        return [
            'name' => 'required',
        ];
    }

    public function translatableRules()
    {
        return [
            'title' => 'string',
        ];
    }
}