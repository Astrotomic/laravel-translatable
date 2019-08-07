# Validation Rule Factory

If you want to validate translated attributes it could get quite complex to list all rules with corresponding attributes their own. To simplify this we've added a `RuleFactory` class. It get's a list of rules and you can mark the key you want to get locale aware. The factory will take the rules assigned to this "placeholder" and copy it to all corresponding locale aware keys.

```php
$rules = [
    'title' => 'required',
    'translations.%content%.body' => 'required',
];

RuleFactory::make($rules);
```

This will return an array which adjusted the `translations.%content%.body` key to match your configured key format. The result will be:

```php
[
    'title' => 'required',
    'translations.en.content.body' => 'required',
    'translations.de.content.body' => 'required',
    'translations.de-DE.content.body' => 'required',
    'translations.de-AT.content.body' => 'required',
]
```

### Configuration

To adjust the default `format` , `prefix` or `suffix` used by the factory you can change them in the package configuration file.

{% code-tabs %}
{% code-tabs-item title="config/translatable.php" %}
```php
'rule_factory' => [
    'format' => \Astrotomic\Translatable\Validation\RuleFactory::FORMAT_ARRAY,
    'prefix' => '%',
    'suffix' => '%',
]
```
{% endcode-tabs-item %}
{% endcode-tabs %}

As `format` we support the two possible variants the `fill()` method supports.

#### RuleFactory::FORMAT\_ARRAY

This will create the dot-notation to support locale sub-arrays. `en.content`.

#### RuleFactory::FORMAT\_KEY

This will create the colon separated style. `content:en`

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

