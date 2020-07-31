<?php

namespace Astrotomic\Translatable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

trait Scopes
{
    public function scopeOrderByTranslation(Builder $query, string $translationField, ?string $locale = null, string $sortMethod = 'asc')
    {
        return $query
            ->with('translations')
            ->select($this->qualifyColumn('*'))
            ->leftJoin(
                $this->getTranslationsTableName(),
                fn (JoinClause $join) => $join
                    ->on($this->qualifyTranslationColumn($this->getTranslationRelationKey()), '=', $this->getQualifiedKeyName())
                    ->when($locale, fn() => $join->where($this->getQualifiedLocaleName(), $locale))
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
            fn (Builder $q) => $q->where($this->getQualifiedLocaleName(), '=', $locale)
        );
    }

    public function scopeNotTranslatedIn(Builder $query, string $locale)
    {
        return $query->whereDoesntHave(
            'translations',
            fn (Builder $q) => $q->where($this->getQualifiedLocaleName(), '=', $locale)
        );
    }

    public function scopeWhereTranslation(Builder $query, string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
    {
        return $query->$method(
            'translations',
            fn (Builder $q) => $q
                ->where($this->qualifyTranslationColumn($translationField), $operator, $value)
                ->when($locale, fn() => $q->where($this->getQualifiedLocaleName(), $operator, $locale))
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

    protected function getQualifiedLocaleName(): string
    {
        return $this->qualifyTranslationColumn($this->getLocaleName());
    }

    protected function qualifyTranslationColumn(string $column): string
    {
        return app($this->getTranslationModelName())->qualifyColumn($column);
    }
}
