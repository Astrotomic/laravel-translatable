# Forms

## Forms

If you want to translate a field in multiple locales with a single form you can use the overridden `fill()` method which allows you to pass in an array with the locales as first array key and the translated attributes in the sub-array.

```php
$post->fill([
  'en' => [
    'title' => 'My first edited post',
  ],
  'de' => [
    'title' => 'Mein erster bearbeiteter Beitrag',
  ],
]);
```

### Request as array syntax

To achieve this structure in your form - to prevent manipulating the form data just to save them. You can use the input name array `[]` [syntax](https://www.php.net/manual/en/faq.html.php#faq.html.arrays).

```markup
<input type="text" name="en[title]" />
<input type="text" name="de[title]" />
```

### Request as plain syntax

If you want, you can structure the request inputs in colon notation too. Notice that with this approach the attribute goes first.

```markup
<input type="text" name="title:en" />
<input type="text" name="title:de" />
```

### Saving the request

Both ways are ready to save the translatable attributes to the model without any other modification.

```php
public function update(Request $request, Post $post)
{
  $post->update($request->all());
}
```

## Updating the default locale

You can update the default locale attributes by accessing the translatable attributes from the model itself.

```php
$post->title = 'My first post'; // Will update the English translatable model
```

```php
$post->fill(['title' => 'My first post']); // Will update the English translatable model
```
