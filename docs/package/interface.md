# Interface

The package provides and interface `\Astrotomic\Translatable\Contracts\Translatable` which describes the public API. It could be that the trait defines more public methods but they aren't part of the defined public API and won't force a new major release if changed or removed.

In addition to the interface we rely on the following methods which will, if changed or removed, also trigger a new major release.

## protected Interface

```php
interface TranslatableProtected
{
    // detect if a given translation attribute value is empty or not
    protected function isEmptyTranslatableAttribute(string $key, $value): bool;

    // save all attached translations
    protected function saveTranslations(): bool;
}
```
