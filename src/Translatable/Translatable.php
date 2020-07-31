<?php

namespace Astrotomic\Translatable;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Contracts\TranslationResolver;
use Astrotomic\Translatable\TranslationResolvers\GivenLocale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @property-read EloquentCollection|Model[] $translations
 * @property-read string $translationModel
 * @property-read string $translationForeignKey
 * @property-read string $localeKey
 * @property-read bool $useTranslationFallback
 *
 * @method static Builder|Translatable|static translated()
 * @method static Builder|Translatable|static translatedIn(?string $locale = null)
 * @method static Builder|Translatable|static notTranslatedIn(?string $locale = null)
 * @method static Builder|Translatable|static whereTranslation(string $translationField, mixed $value, ?string $locale = null)
 * @method static Builder|Translatable|static whereTranslationLike(string $translationField, mixed $value, ?string $locale = null)
 * @method static Builder|Translatable|static orWhereTranslation(string $translationField, mixed $value, ?string $locale = null)
 * @method static Builder|Translatable|static orWhereTranslationLike(string $translationField, mixed $value, ?string $locale = null)
 * @method static Builder|Translatable|static orderByTranslation(string $translationField, ?string $locale = null, string $sortMethod = 'asc')
 *
 * @mixin Model
 */
trait Translatable
{
    protected static $autoloadTranslations = null;

    protected static $deleteTranslationsCascade = false;

    protected $enforcedLocale;

    public static function bootTranslatable(): void
    {
        static::saved(function (Model $model) {
            /* @var Translatable $model */
            return $model->saveTranslations();
        });

        static::deleting(function (Model $model): void {
            /* @var Translatable $model */
            if (self::$deleteTranslationsCascade === true) {
                $model->deleteTranslations();
            }
        });
    }

    public static function defaultAutoloadTranslations(): void
    {
        self::$autoloadTranslations = null;
    }

    public static function disableAutoloadTranslations(): void
    {
        self::$autoloadTranslations = false;
    }

    public static function enableAutoloadTranslations(): void
    {
        self::$autoloadTranslations = true;
    }

    public static function disableDeleteTranslationsCascade(): void
    {
        self::$deleteTranslationsCascade = false;
    }

    public static function enableDeleteTranslationsCascade(): void
    {
        self::$deleteTranslationsCascade = true;
    }

    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        if (
            (! $this->relationLoaded('translations') && ! $this->toArrayAlwaysLoadsTranslations() && is_null(self::$autoloadTranslations))
            || self::$autoloadTranslations === false
        ) {
            return $attributes;
        }

        $hiddenAttributes = $this->getHidden();

        foreach ($this->translatedAttributes as $field) {
            if (in_array($field, $hiddenAttributes)) {
                continue;
            }

            $attributes[$field] = $this->getAttributeOrFallback(null, $field);
        }

        return $attributes;
    }

    /**
     * @param string|string[]|null $locales The locales to be deleted
     *
     * @return static
     */
    public function deleteTranslations($locales = null): self
    {
        $this->translations()
            ->when($locales !== null, fn (Builder $query) => $query->whereIn($this->getLocaleName(), Arr::wrap($locales)))
            ->cursor()
            ->each(fn (Model $translation) => $translation->delete());

        // we need to manually "reload" the collection built from the relationship
        // otherwise $this->translations()->get() would NOT be the same as $this->translations
        return $this->load('translations');
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $values) {
            if (
                $this->getLocalesHelper()->has($key)
                && is_array($values)
            ) {
                $this->getTranslationOrNew($key)->fill($values);
                unset($attributes[$key]);
            } else {
                [$attribute, $locale] = $this->getAttributeAndLocale($key);

                if (
                    $this->getLocalesHelper()->has($locale)
                    && $this->isTranslationAttribute($attribute)
                ) {
                    $this->getTranslationOrNew($locale)->fill([$attribute => $values]);
                    unset($attributes[$key]);
                }
            }
        }

        return parent::fill($attributes);
    }

    public function getAttribute($key)
    {
        [$attribute, $locale] = $this->getAttributeAndLocale($key);

        if ($this->isTranslationAttribute($attribute)) {
            return $this->transformModelValue(
                $attribute,
                $this->getAttributeOrFallback($locale, $attribute)
            );
        }

        return parent::getAttribute($key);
    }

    public function getEnforcedLocale(): ?string
    {
        return $this->enforcedLocale;
    }

    public function getLocaleName(): string
    {
        return $this->localeKey ?: config('translatable.locale_key', 'locale');
    }

    public function getNewTranslation(string $locale): Model
    {
        $modelName = $this->getTranslationModelName();

        /** @var Model $translation */
        $translation = new $modelName();
        $translation->setAttribute($this->getLocaleName(), $locale);
        $this->translations->add($translation);

        return $translation;
    }

    public function getTranslation(?string $locale = null, bool $withFallback = null): ?Model
    {
        $locale = $locale ?: $this->locale();
        $withFallback = $withFallback ?? $this->useFallback();
        $alreadyCheckedLocales = collect([]);

        foreach ($this->getTranslationResolvers() as $resolver) {
            $translation = $resolver->resolve($this, $locale, $withFallback, $alreadyCheckedLocales);

            if ($translation instanceof Model) {
                return $translation;
            }
        }

        return null;
    }

    public function getTranslationOrNew(?string $locale = null): Model
    {
        $locale = $locale ?: $this->locale();

        if (($translation = $this->getTranslation($locale, false)) === null) {
            $translation = $this->getNewTranslation($locale);
        }

        return $translation;
    }

    public function getTranslationOrFail(string $locale): Model
    {
        if (($translation = $this->getTranslation($locale, false)) === null) {
            throw (new ModelNotFoundException)->setModel($this->getTranslationModelName(), $locale);
        }

        return $translation;
    }

    public function getTranslationsArray(): array
    {
        $translations = [];

        foreach ($this->translations as $translation) {
            foreach ($this->translatedAttributes as $attr) {
                $translations[$translation->{$this->getLocaleName()}][$attr] = $translation->{$attr};
            }
        }

        return $translations;
    }

    public function hasTranslation(?string $locale = null): bool
    {
        $locale = $locale ?: $this->locale();

        foreach ($this->translations as $translation) {
            if ($translation->getAttribute($this->getLocaleName()) == $locale) {
                return true;
            }
        }

        return false;
    }

    public function isTranslationAttribute(string $key): bool
    {
        return in_array($key, $this->translatedAttributes);
    }

    /**
     * @param array|null $except
     *
     * @return static|TranslatableContract
     */
    public function replicateWithTranslations(?array $except = null): self
    {
        $newInstance = $this->replicate($except);

        unset($newInstance->translations);
        foreach ($this->translations as $translation) {
            $newTranslation = $translation->replicate();
            $newInstance->translations->add($newTranslation);
        }

        return $newInstance;
    }

    public function setAttribute($key, $value): self
    {
        [$attribute, $locale] = $this->getAttributeAndLocale($key);

        if ($this->isTranslationAttribute($attribute)) {
            $this->getTranslationOrNew($locale)->$attribute = $value;

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    public function setEnforcedLocale(?string $locale)
    {
        $this->enforcedLocale = $locale;

        return $this;
    }

    public function translate(?string $locale = null, bool $withFallback = false): ?Model
    {
        return $this->getTranslation($locale, $withFallback);
    }

    public function translateOrDefault(?string $locale = null): ?Model
    {
        return $this->getTranslation($locale, true);
    }

    public function translateOrNew(?string $locale = null): Model
    {
        return $this->getTranslationOrNew($locale);
    }

    public function translateOrFail(string $locale): Model
    {
        return $this->getTranslationOrFail($locale);
    }

    protected function getLocalesHelper(): Locales
    {
        return app(Locales::class);
    }

    public function isEmptyTranslatableAttribute(string $key, $value): bool
    {
        return empty($value);
    }

    protected function isTranslationDirty(TranslatableContract $translation): bool
    {
        $dirtyAttributes = $translation->getDirty();
        unset($dirtyAttributes[$this->getLocaleName()]);

        return ! empty($dirtyAttributes);
    }

    protected function locale(): string
    {
        if ($this->getEnforcedLocale()) {
            return $this->getEnforcedLocale();
        }

        return $this->getLocalesHelper()->current();
    }

    protected function saveTranslations(): bool
    {
        $saved = true;

        if (! $this->relationLoaded('translations')) {
            return $saved;
        }

        foreach ($this->translations as $translation) {
            if ($saved && $this->isTranslationDirty($translation)) {
                if (! empty($connectionName = $this->getConnectionName())) {
                    $translation->setConnection($connectionName);
                }

                $translation->setAttribute($this->getTranslationRelationKey(), $this->getKey());
                $saved = $translation->save();
            }
        }

        return $saved;
    }

    protected function getAttributeAndLocale(string $key): array
    {
        if (Str::contains($key, ':')) {
            return explode(':', $key);
        }

        return [$key, $this->locale()];
    }

    protected function getAttributeOrFallback(?string $locale, string $attribute)
    {
        $locale = $locale ?: $this->locale();
        $withFallback = $this->usePropertyFallback();
        $alreadyCheckedLocales = collect([]);

        foreach ($this->getTranslationResolvers() as $resolver) {
            $translation = $resolver->resolveWithAttribute($this, $locale, $withFallback, $alreadyCheckedLocales, $attribute);

            if ($translation instanceof Translatable) {
                return $translation->$attribute;
            }
        }

        return null;
    }

    protected function toArrayAlwaysLoadsTranslations(): bool
    {
        return config('translatable.to_array_always_loads_translations', true);
    }

    protected function useFallback(): bool
    {
        if (isset($this->useTranslationFallback) && is_bool($this->useTranslationFallback)) {
            return $this->useTranslationFallback;
        }

        return (bool) config('translatable.use_fallback', false);
    }

    protected function usePropertyFallback(): bool
    {
        return $this->useFallback() && config('translatable.use_property_fallback', false);
    }

    /**
     * @return TranslationResolver[]
     */
    protected function getTranslationResolvers(): array
    {
        $resolvers = config('translatable.translation_resolvers', []);

        if (! in_array(GivenLocale::class, $resolvers)) {
            $resolvers = Arr::prepend($resolvers, GivenLocale::class);
        }

        return $this->makeTranslationResolvers($resolvers);
    }

    /**
     * @param string[] $resolvers
     *
     * @return TranslationResolver[]
     */
    protected function makeTranslationResolvers(array $resolvers): array
    {
        return array_map(
            fn (string $resolver): TranslationResolver => app($resolver),
            $resolvers
        );
    }

    public function __isset($key)
    {
        return $this->isTranslationAttribute($key) || parent::__isset($key);
    }

    protected function getTranslationModelName(): string
    {
        return $this->translationModel ?: $this->getTranslationModelNameDefault();
    }

    protected function getTranslationModelNameDefault(): string
    {
        $modelName = get_class($this);
        $namespace = $this->getTranslationModelNamespace();

        if (! empty($namespace)) {
            $modelName = $namespace.'\\'.class_basename($modelName);
        }

        return $modelName.config('translatable.translation_suffix', 'Translation');
    }

    protected function getTranslationModelNamespace(): ?string
    {
        return config('translatable.translation_model_namespace');
    }

    protected function getTranslationRelationKey(): string
    {
        if ($this->translationForeignKey) {
            return $this->translationForeignKey;
        }

        return $this->getForeignKey();
    }

    public function translations(): HasMany
    {
        return $this->hasMany($this->getTranslationModelName(), $this->getTranslationRelationKey());
    }

    public function scopeOrderByTranslation(Builder $query, string $translationField, ?string $locale = null, string $sortMethod = 'asc')
    {
        return $query
            ->with('translations')
            ->select($this->qualifyColumn('*'))
            ->leftJoin(
                $this->getTranslationsTableName(),
                fn (JoinClause $join) => $join
                    ->on($this->qualifyTranslationColumn($this->getTranslationRelationKey()), '=', $this->getQualifiedKeyName())
                    ->when($locale, fn () => $join->where($this->getQualifiedLocaleName(), $locale))
            )
            ->orderBy($this->qualifyTranslationColumn($translationField), $sortMethod);
    }

    public function scopeTranslated(Builder $query)
    {
        return $query->has('translations');
    }

    public function scopeTranslatedIn(Builder $query, string $locale)
    {
        return $query->whereHas(
            'translations',
            fn (Builder $q) => $q->where($this->getQualifiedLocaleName(), '=', $locale ?? $this->locale())
        );
    }

    public function scopeNotTranslatedIn(Builder $query, string $locale)
    {
        return $query->whereDoesntHave(
            'translations',
            fn (Builder $q) => $q->where($this->getQualifiedLocaleName(), '=', $locale ?? $this->locale())
        );
    }

    public function scopeWhereTranslation(Builder $query, string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
    {
        return $query->$method(
            'translations',
            fn (Builder $q) => $q
                ->where($this->qualifyTranslationColumn($translationField), $operator, $value)
                ->when($locale, fn () => $q->where($this->getQualifiedLocaleName(), $operator, $locale))
        );
    }

    public function scopeWhereTranslationLike(Builder $query, string $translationField, $value, ?string $locale = null)
    {
        return $this->scopeWhereTranslation($query, $translationField, $value, $locale, 'whereHas', 'LIKE');
    }

    public function scopeOrWhereTranslation(Builder $query, string $translationField, $value, ?string $locale = null)
    {
        return $this->scopeWhereTranslation($query, $translationField, $value, $locale, 'orWhereHas');
    }

    public function scopeOrWhereTranslationLike(Builder $query, string $translationField, $value, ?string $locale = null)
    {
        return $this->scopeWhereTranslation($query, $translationField, $value, $locale, 'orWhereHas', 'LIKE');
    }

    protected function getTranslationsTableName(): string
    {
        return app($this->getTranslationModelName())->getTable();
    }

    public function getQualifiedLocaleName(): string
    {
        return $this->qualifyTranslationColumn($this->getLocaleName());
    }

    protected function qualifyTranslationColumn(string $column): string
    {
        return app($this->getTranslationModelName())->qualifyColumn($column);
    }
}
