<?php

namespace Astrotomic\Translatable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;

trait Scopes
{
    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeListsTranslations(Builder $query, string $translationField): Builder
    {
        $withFallback = $this->useFallback();
        $translationTable = $this->getTranslationsTable();
        $localeKey = $this->getLocaleKey();

        $query
            ->select($this->getTable().'.'.$this->getKeyName(), $translationTable.'.'.$translationField)
            ->leftJoin($translationTable, $translationTable.'.'.$this->getTranslationRelationKey(), '=', $this->getTable().'.'.$this->getKeyName())
            ->where($translationTable.'.'.$localeKey, $this->locale());

        if ($withFallback) {
            $query->orWhere(function (Builder $q) use ($translationTable, $localeKey) {
                $q
                    ->where($translationTable.'.'.$localeKey, $this->getFallbackLocale())
                    ->whereNotIn($translationTable.'.'.$this->getTranslationRelationKey(), function (QueryBuilder $q) use (
                        $translationTable,
                        $localeKey
                    ) {
                        $q
                            ->select($translationTable.'.'.$this->getTranslationRelationKey())
                            ->from($translationTable)
                            ->where($translationTable.'.'.$localeKey, $this->locale());
                    });
            });
        }

        return $query;
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeNotTranslatedIn(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?: $this->locale();

        return $query->whereDoesntHave('translations', function (Builder $q) use ($locale) {
            $q->where($this->getLocaleKey(), '=', $locale);
        });
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOrderByTranslation(Builder $query, string $translationField, string $sortMethod = 'asc'): Builder
    {
        $translationTable = $this->getTranslationsTable();
        $localeKey = $this->getLocaleKey();
        $table = $this->getTable();
        $keyName = $this->getKeyName();

        return $query
            ->with('translations')
            ->select("{$table}.*")
            ->leftJoin($translationTable, function (JoinClause $join) use ($translationTable, $localeKey, $table, $keyName) {
                $join
                    ->on("{$translationTable}.{$this->getTranslationRelationKey()}", '=', "{$table}.{$keyName}")
                    ->where("{$translationTable}.{$localeKey}", $this->locale());
            })
            ->orderBy("{$translationTable}.{$translationField}", $sortMethod);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOrWhereTranslation(Builder $query, string $translationField, mixed $value, ?string $locale = null): Builder
    {
        return $this->scopeWhereTranslation($query, $translationField, $value, $locale, 'orWhereHas');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOrWhereTranslationLike(Builder $query, string $translationField, mixed $value, ?string $locale = null): Builder
    {
        return $this->scopeWhereTranslation($query, $translationField, $value, $locale, 'orWhereHas', 'LIKE');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeTranslated(Builder $query): Builder
    {
        return $query->has('translations');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeTranslatedIn(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?: $this->locale();

        return $query->whereHas('translations', function (Builder $q) use ($locale) {
            $q->where($this->getLocaleKey(), '=', $locale);
        });
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWhereTranslation(Builder $query, string $translationField, mixed $value, ?string $locale = null, string $method = 'whereHas', string $operator = '='): Builder
    {
        return $query->$method('translations', function (Builder $query) use ($translationField, $value, $locale, $operator) {
            $query->where($this->getTranslationsTable().'.'.$translationField, $operator, $value);

            if ($locale) {
                $query->where($this->getTranslationsTable().'.'.$this->getLocaleKey(), $operator, $locale);
            }
        });
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWhereTranslationLike(Builder $query, string $translationField, mixed $value, ?string $locale = null): Builder
    {
        return $this->scopeWhereTranslation($query, $translationField, $value, $locale, 'whereHas', 'LIKE');
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeWithTranslation(Builder $query, ?string $locale = null): void
    {
        $locale = $locale ?: $this->locale();

        $query->with([
            'translations' => function (Relation $query) use ($locale) {
                if ($this->useFallback()) {
                    $countryFallbackLocale = $this->getFallbackLocale($locale); // e.g. de-DE => de
                    $locales = array_unique([$locale, $countryFallbackLocale, $this->getFallbackLocale()]);

                    return $query->whereIn($this->getTranslationsTable().'.'.$this->getLocaleKey(), $locales);
                }

                return $query->where($this->getTranslationsTable().'.'.$this->getLocaleKey(), $locale);
            },
        ]);
    }

    protected function getTranslationsTable(): string
    {
        return app()->make($this->getTranslationModelName())->getTable();
    }
}
