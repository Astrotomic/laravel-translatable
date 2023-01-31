# Validation Rule Factory

If you want to validate translated attributes it could get quite complex to list all rules with corresponding attributes their own. To simplify this we've added a `RuleFactory` class. It get's a list of rules and you can mark the key you want to get locale aware. The factory will take the rules assigned to this "placeholder" and copy it to all corresponding locale aware keys.

```php
$rules = RuleFactory::make([
    'translations.%title%' => 'sometimes|string',
    'translations.%content%' => ['required_with:translations.%title%', 'string'],
]);

$validatedData = $request->validate($rules);
```

This will return an array which adjusted the placeholder in key and string value or array with strings to match your configured key format. The result will be:

```php
[
    'translations.en.title' => 'sometimes|string',
    'translations.de.title' => 'sometimes|string',
    'translations.de-DE.title' => 'sometimes|string',
    'translations.de-AT.title' => 'sometimes|string',

    'translations.en.content' => ['required_with:translations.en.title', 'string'],
    'translations.de.content' => ['required_with:translations.de.title', 'string'],
    'translations.de-DE.content' => ['required_with:translations.de-DE.title', 'string'],
    'translations.de-AT.content' => ['required_with:translations.de-AT.title', 'string'],
]
```

### Configuration

To adjust the default `format` , `prefix` or `suffix` used by the factory you can change them in the package configuration file.

{% code title="config/translatable.php" %}

```php
'rule_factory' => [
    'format' => \Astrotomic\Translatable\Validation\RuleFactory::FORMAT_ARRAY,
    'prefix' => '%',
    'suffix' => '%',
]
```

{% endcode %}

As `format` we support the two possible variants the `fill()` method supports.

#### RuleFactory::FORMAT_ARRAY

This will create the dot-notation to support locale sub-arrays. `en.content`.

#### RuleFactory::FORMAT_KEY

This will create the colon separated style. `content:en`

{% hint style="info" %}
If you use the key format and want to use use it as argument for a rule you have to wrap it in quotes. `required_with:"translations.content:en"`
{% endhint %}

### Runtime

For sure you can change the default `format`, `prefix`, `suffix` and applied `locales` during runtime. To do so only pass them as parameter to the `make()` method.

```php
RuleFactory::make($rules, RuleFactory::FORMAT_KEY, '{', '}', [
    'en',
    'de',
]);
```

This will use the colon style, use `{` and `}` as delimiter and use only `de` and `en` as locales.

{% hint style="info" %}
You can only use defined locales. Every locale that's not in `Locales::all()`will throw an `InvalidArgumentException`.
{% endhint %}
