# Locales helper

In addition to the trait we also provide a helper class `\Astrotomic\Translatable\Locales` it's a singleton service available as `translatable.locales` and `\Astrotomic\Translatable\Locales` .  
This helper manages all locales available for translation and provides several methods.

This class also implements the `ArrayAccess` interface to allow you to use it like a normal array.

## Methods

### load\(\)

This method will \(re\)load all locales from the `translatable.locales` config - it's called during service instantiation so you will only need it if you change the config during runtime.

{% hint style="info" %}
If you don't have to do so to work with other packages we recommend to use the provided methods of this helper service to manipulate the available locales instead of changing the config during runtime.
{% endhint %}

### all\(\)

**Alias:** `toArray()`

Returns all available locales as an array - the structure differs from the config one, the return final generated array with combined country locales.

```php
[
  'en',
  'de',
  'es',
  'es-MX',
  'es-CO',
]
```

### current\(\)

Returns the current locale string.

### has\(string $locale\)

Checks if the given locale is available in the configured set of locales.

### get\(string $locale\)

Returns the provided locale or `null` if it's not set.

{% hint style="info" %}
At all this isn't really useful except you want to build your own check if a locale is set.
{% endhint %}

### add\(string $locale\)

Adds the given locale to the set of available locales.

{% hint style="info" %}
The set of available locales will keep unique and this method won't throw an exception if the locale is already present.
{% endhint %}

### forget\(string $locale\)

Removes the given locale of the available locales set.

{% hint style="info" %}
This method won't throw an exception if the locale isn't present.
{% endhint %}

### getLocaleSeparator\(\)

Returns the configured `translatable.locale_separator` locale separator used to combine language with country locales.

### getCountryLocale\(string $locale, string $country\)

Returns the formatted country based locale.

### isLocaleCountryBased\(string $locale\)

Checks if the given locale is a country specific locale.

### getLanguageFromCountryBasedLocale\(string $locale\)

Returns the language locale from given country based locale.
