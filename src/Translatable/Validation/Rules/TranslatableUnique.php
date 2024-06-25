<?php

declare(strict_types=1);

namespace Astrotomic\Translatable\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\InvokableRule;

/**
 * Custom unique validation for translatable attributes
 *
 * @TODO should be updated to use use Illuminate\Contracts\Validation\ValidationRule; when this package drop of Laravel 9 support
 * instead using detracted interface InvokableRule
 *
 * @author Amjad BaniMattar <amjad.banimattar@gmail.com>
 */
class TranslatableUnique extends TranslatableExists implements InvokableRule
{
    /**
     * Validate if the given attribute is unique.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! empty($value)) {
            $query = $this->model::whereTranslation($this->field, $value, $this->locale);
            if ($this->ignore) {
                $query->whereNot($this->idColumn, $this->ignore);
            }
            $exists = $query->exists();

            if ($exists) {
                $fail('translatable::validation.translatableUnique')->translate();
            }
        }
    }

    /**
     * Laravel 9 compatibility (InvokableRule interface)
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function __invoke($attribute, $value, $fail): void
    {
        $this->validate($attribute, $value, $fail);
    }
}
