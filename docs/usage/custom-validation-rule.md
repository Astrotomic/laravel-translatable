### **Validating Unique and Exists Rule**

You can use custom rules to validate unique and exists rules for translatable attributes.

#### TranslatableUnique

Ensure that the attribute value is unique by checking its absence in the database; if the value already exists, raise a validation exception.

##### Option 1

```php
use Astrotomic\Translatable\Validation\Rules\TranslatableUnique;
...

$person = new Person(['name' => 'john doe']);
$person->save();

$data = [
    'name' => 'john doe',
    'email' => 'john@example.com'
];
$validator = Validator::make($data, [
    'name' => ['required', new TranslatableUnique(Person::class, 'name')],
]);

```

##### Option 2

```php
use Astrotomic\Translatable\Validation\Rules\TranslatableUnique;
...

$person = new Person(['name' => 'john doe']);
$person->save();

$data = [
    'name:en' => 'john doe',
    'email' => 'john@example.com'
];

$validator = Validator::make($data, [
    'name:en' => ['required', Rule::translatableUnique(Person::class, 'name:en')],
]);

```

##### Option 2

```php
use Illuminate\Validation\Rule;
...

$person = new Person(['name' => 'john doe']);
$person->save();

$data = [
    'name:en' => 'john doe',
    'email' => 'john@example.com'
];

$validator = Validator::make($data, [
    'name:en' => ['required', Rule::translatableUnique(Person::class, 'name:en')],
]);

```


#### TranslatableExists

Verify if the attribute value exists by confirming its presence in the database; if the value does not exist, raise a validation exception.


##### Option 1
```php
use Astrotomic\Translatable\Validation\Rules\TranslatableExists;
...

$person = new Person(['name' => 'john doe']);
$person->save();

$data = [
    'name' => 'john doe',
    'email' => 'john@example.com'
];
$validator = Validator::make($data, [
    'name' => ['required', new TranslatableExists(Person::class, 'name')],
]);
```

##### Option 2
```php
use Illuminate\Validation\Rule;
...

$person = new Person(['name' => 'john doe']);
$person->save();

$data = [
    'name:en' => 'john doe',
    'email' => 'john@example.com'
];

$validator = Validator::make($data, [
    'name:en' => ['required', Rule::translatableExists(Person::class, 'name:en')],
]);
```