# Scopes

## translatedIn\(?string $locale = null\)

Returns all posts being translated in english

```php
Post::translatedIn('en')->get();
```

## notTranslatedIn\(?string $locale = null\)

Returns all posts not being translated in english

```php
Post::notTranslatedIn('en')->get();
```

## translated\(\)

Returns all posts not being translated in any locale

```php
Post::translated()->get();
```

## withTranslation\(\)

Eager loads translation relationship only for the default and fallback \(if enabled\) locale

```php
Post::withTranslation()->get();
```

## listTranslations\(string $translationField\)

Returns an array containing pairs of post ids and the translated title attribute

```php
Post::listsTranslations('title')->get()->toArray();
```

```php
[
    ['id' => 1, 'title' => 'My first post'],
    ['id' => 2, 'title' => 'My second post']
]
```

## where translation

Filters posts by checking the translation against the given value

### whereTranslation\(string $translationField, $value, ?string $locale = null\)

```php
Post::whereTranslation('title', 'My first post')->first();
```

### orWhereTranslation\(string $translationField, $value, ?string $locale = null\)

```php
Post::whereTranslation('title', 'My first post')
    ->orWhereTranslation('title', 'My second post')
    ->get();
```

### whereTranslationLike\(string $translationField, $value, ?string $locale = null\)

```php
Post::whereTranslationLike('title', '%first%')->first();
```

### orWhereTranslationLike\(string $translationField, $value, ?string $locale = null\)

```php
Post::whereTranslationLike('title', '%first%')
    ->orWhereTranslationLike('title', '%second%')
    ->get();
```

## orderByTranslation\(string $translationField, string $sortMethod = 'asc'\)

Sorts the model by a given translation column value

```php
Post::orderByTranslation('title')->get()
```
