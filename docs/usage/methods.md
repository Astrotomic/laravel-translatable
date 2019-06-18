# Methods

## Preconditions

* the `locale` is set to `de`
* the `fallback_locale` is set to `en`
* our main model instance is `$post = Post::first()`
* translations are available for `en`, `de` and `fr`

## Get an instance of the translation model

### translate\(?string $locale = null, bool $withFallback = false\)

**Alias of:** `getTranslation(?string $locale = null, bool $withFallback = null)`

This returns an instance of `PostTranslation` using the default or given locale. It can also use the configured fallback locale if first locale isn't present.

```php
$post->translate(); // returns the german translation model
$post->translate('fr'); // returns the french translation model
$post->translate('it'); // returns null
$post->translate('it', true); // returns the english translation model
```

### translateOrDefault\(?string $locale = null\)

**Alias of:** `getTranslation(?string $locale = null, bool $withFallback = null)`

This returns an instance of `PostTranslation` using the default or given locale and will always use fallback if needed.

```php
$post->translateOrDefault(); // returns the german translation model
$post->translateOrDefault('fr'); // returns the french translation model
$post->translateOrDefault('it'); // returns the english translation model
```

### translateOrNew\(?string $locale = null\)

**Alias of:** `getTranslationOrNew(?string $locale = null)`

This returns an instance of `PostTranslation` using the default or given locale and will create a new instance if needed.

```php
$post->translateOrNew(); // returns the german translation model
$post->translateOrNew('fr'); // returns the french translation model
$post->translateOrNew('it'); // returns the new italian translation model
```

## hasTranslation\(?string $locale = null\)

Check if the post has a translation in default or given locale.

```php
$post->hasTranslation(); // true
$post->hasTranslation('fr'); // true
$post->hasTranslation('it'); // false
```

## translations\(\)

Is the eloquent relation method for the `HasMany` relation to the translation model.

## deleteTranslations\(string\|array $locales = null\)

Deletes all translations for the given locale\(s\).

```php
$post->deleteTranslations(); // delete all translations
$post->deleteTranslations('de'); // delete german translation
$post->deleteTranslations(['de', 'en']); // delete german and english translation
```

## getTranslationsArray\(\)

Returns all the translations as array - the structure is the same as it's accepted by the `fill(array $data)` method.

```php
$post->getTranslationsArray();
// Returns
[
 'en' => ['title' => 'My first post'],
 'de' => ['title' => 'Mein erster Beitrag'],
 'fr' => ['title' => 'Mon premier post'],
];
```

## replicateWithTranslations\(array $except = null\)

Creates a clone and clones the translations.

```php
$replicate = $post->replicateWithTranslations(); 
```

