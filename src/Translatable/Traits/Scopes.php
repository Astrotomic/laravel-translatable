<?php

namespace Astrotomic\Translatable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;

trait Scopes
{
    public function scopeListsTranslations(Builder $query, string $translationField)
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

    public function scopeNotTranslatedIn(Builder $query, ?string $locale = null)
    {
        $locale = $locale ?: $this->locale();

        return $query->whereDoesntHave('translations', function (Builder $q) use ($locale) {
            $q->where($this->getLocaleKey(), '=', $locale);
        });
    }

    public function scopeOrderByTranslation(Builder $query, string $translationField, string $sortMethod = 'asc')
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

    public function scopeOrWhereTranslation(Builder $query, string $translationField, $value, ?string $locale = null)
    {
        return $this->scopeWhereTranslation($query, $translationField, $value, $locale, 'orWhereHas');
    }

    public function scopeOrWhereTranslationLike(Builder $query, string $translationField, $value, ?string $locale = null)
    {
        return $this->scopeWhereTranslation($query, $translationField, $value, $locale, 'orWhereHas', 'LIKE');
    }

    public function scopeTranslated(Builder $query)
    {
        return $query->has('translations');
    }

    public function scopeTranslatedIn(Builder $query, ?string $locale = null)
    {
        $locale = $locale ?: $this->locale();

        return $query->whereHas('translations', function (Builder $q) use ($locale) {
            $q->where($this->getLocaleKey(), '=', $locale);
        });
    }

    public function scopeWhereTranslation(Builder $query, string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
    {
        return $query->$method('translations', function (Builder $query) use ($translationField, $value, $locale, $operator) {
            $query->where($this->getTranslationsTable().'.'.$translationField, $operator, $value);

            if ($locale) {
                $query->where($this->getTranslationsTable().'.'.$this->getLocaleKey(), $operator, $locale);
            }
        });
    }

    public function scopeWhereTranslationLike(Builder $query, string $translationField, $value, ?string $locale = null)
    {
        return $this->scopeWhereTranslation($query, $translationField, $value, $locale, 'whereHas', 'LIKE');
    }

    public function scopeWithTranslation(Builder $query, ?string $locale = null)
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
