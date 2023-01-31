# Fallback locale

## App wide

If you want to fallback to a default translation when a translation has not been found, enable this in the configuration using the `use_fallback` key. And to select the default locale, use the `fallback_locale` key.

```php
[
    // ...
    'use_fallback' => true,
    'fallback_locale' => 'en',
    // ...
]
```

If you do not want to define the default fallback locale but just get the first \(in order of configuration\) defined one you can set `fallback_locale` to `null`.

```php
[
    // ...
    'use_fallback' => true,
    'fallback_locale' => null,
    'locales' => [
        'en',
        'de' => [
            'DE',
            'AT',
            'CH',
        ],
    ]
    // ...
]
```

This configuration will check the locales in following order and return the first defined one:

1. `en`
2. `de`
3. `de-DE`
4. `de-AT`
5. `de-CH`

{% hint style="info" %}
The simple language based locale will be checked before the assigned country based ones.
{% endhint %}

## per Model

You can also define per-model the default for "if fallback should be used", by setting the `$useTranslationFallback`property:

```php
class Post extends Model
{
    public $useTranslationFallback = true;
}
```

## for Properties

Even though we try having all models nicely translated, some fields might left empty. What's the result? You end up with missing translations for those fields!

The property fallback feature is here to help. When enabled, translatable will return the value of the fallback language for those empty properties.

The feature is enabled by default on new installations. If your config file was setup before v7.1, make sure to add the following line to enable the feature:

```php
'use_property_fallback' => true,
```

Of course the fallback locales must be enabled to use this feature.

If the property fallback is enabled in the configuration, then translatable will return the translation of the fallback locale for the fields where the translation is empty.

### **customize empty translation property detection**

This package is made to translate strings, but in general it's also able to translate numbers, bools or whatever you want to. By default a simple `empty()` call is used to detect if the translation value is empty or not. If you want to customize this or use different logic per property you can override `isEmptyTranslatableAttribute()` in your main model.

```php
protected function isEmptyTranslatableAttribute(string $key, $value): bool
{
    switch($key) {
        case 'name':
            return empty($value);
        case 'price':
            return !is_number($value);
        default:
            return is_null($value);
    }
}
```

## **Country based fallback**

Since version v5.3 it is possible to use country based locales. For example, you can have the following locales:

- English: `en`
- Spanish: `es`
- Mexican Spanish: `es-MX`
- Colombian Spanish: `es-CO`

To configuration for these locales looks like this:

```php
    'locales' => [
        'en',
        'es' => [
            'MX',
            'CO',
        ],
    ];
```

We can also configure the "glue" between the language and country. If for instance we prefer the format `es_MX` instead of `es-MX`, the configuration should look like this:

```php
'locale_separator' => '_',
```

What applies for the fallback of the locales using the `en-MX` format?

Let's say our fallback locale is `en`. Now, when we try to fetch from the database the translation for the locale `es-MX`but it doesn't exist, we won't get as fallback the translation for `en`. Translatable will use as a fallback `es` \(the first part of `es-MX`\) and only if nothing is found, the translation for `en` is returned.
