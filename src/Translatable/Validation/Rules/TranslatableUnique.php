<?php

declare(strict_types=1);

namespace Astrotomic\Translatable\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Custom unique validation for translatable attributes
 *
 * @author Amjad BaniMattar <amjad.banimattar@gmail.com>
 */
class TranslatableUnique extends TranslatableExists implements ValidationRule
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
}
