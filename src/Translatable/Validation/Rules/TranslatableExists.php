<?php

declare(strict_types=1);

namespace Astrotomic\Translatable\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
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
     */
    protected null|int|string $ignore = null;

    /**
     * The name of the ID column of ignored model.
     */
    protected string $idColumn = 'id';

    /**
     * The default locale
     */
    protected ?string $locale = null;

    /**
     * @param  class-string<Model>  $model
     * @param  string  $field  The field to check for existents
     */
    public function __construct(protected string $model, protected string $field)
    {
        if (! class_exists($model)) {
            throw new \Exception("Class '$model' does not exist.");
        }

        if (Str::contains($field, ':')) {
            [$this->field, $this->locale] = explode(':', $field);
        }
    }

    /**
     * Ignore the given ID during the unique check.
     *
     * @return $this
     */
    public function ignore(int|string|Model $id, ?string $idColumn = null): self
    {
        if ($id instanceof Model) {
            return $this->ignoreModel($id, $idColumn);
        }

        $this->ignore = $id;
        $this->idColumn = $idColumn ?? ((new $this->model())->getKeyName());

        return $this;
    }

    /**
     * Ignore the given model during the unique check.
     *
     * @return $this
     */
    public function ignoreModel(Model $model, ?string $idColumn = null): self
    {
        $this->idColumn = $idColumn ?? $model->getKeyName();
        $this->ignore = $model->{$this->idColumn};

        return $this;
    }

    /**
     * Validate the given attribute against the exists constraint, or throw ValidationException.
     *
     * @param  string  $attribute  attribute name
     * @param  mixed  $value  attribute value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! empty($value)) {
            $exists = $this->model::query()
                ->whereTranslation($this->field, $value, $this->locale)
                ->when(
                    $this->ignore,
                    fn (Builder $query) => $query->whereNot($this->idColumn, $this->ignore)
                )
                ->exists();

            if (! $exists) {
                $fail('translatable::validation.translatableExist')->translate();
            }
        }
    }
}
