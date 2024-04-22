<?php

declare(strict_types=1);

namespace Astrotomic\Translatable\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Custom exists validation for translatable attributes
 * 
 * @author Amjad BaniMattar <amjad.banimattar@gmail.com>
 */
class TranslatableExists implements ValidationRule
{
    /**
     * The ID that should be ignored.
     *
     * @var mixed
     */
    protected mixed $ignore = null;

    /**
     * The name of the ID column.
     *
     * @var string
     */
    protected string $idColumn = 'id';

    /**
     * The default locale
     * 
     * @var string
     */
    protected ?string $locale = null;

    public function __construct(private string $model, private string $field)
    {
        if (Str::contains($field, ':')) {
            [$this->field, $this->locale] = explode(':', $field);
        }
        //
    }

    /**
     * Ignore the given ID during the unique check.
     *
     * @param  mixed  $id
     * @param  string|null  $idColumn
     * @return $this
     */
    public function ignore(mixed $id, ?string $idColumn = null): self
    {
        if ($id instanceof Model) {
            return $this->ignoreModel($id, $idColumn);
        }

        $this->ignore = $id;
        $this->idColumn = $idColumn ?? 'id';

        return $this;
    }

    /**
     * Ignore the given model during the unique check.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string|null  $idColumn
     * @return $this
     */
    public function ignoreModel(Model $model, ?string $idColumn = null): self
    {
        $this->idColumn = $idColumn ?? $model->getKeyName();
        $this->ignore = $model->{$this->idColumn};

        return $this;
    }

    /**
     * Run the validation rule.
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

            if (! $exists) {
                $fail('translatable::validation.translatableExist')->translate();
            }
        }
    }
}
