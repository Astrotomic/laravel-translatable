# Forms

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

To achieve this structure in your form - to prevent manipulating the form data just to save them. You can use the input name array `[]` [syntax](https://www.php.net/manual/en/faq.html.php#faq.html.arrays).

```html
<input type="text" name="en[title]" />
<input type="text" name="de[title]" />
```

# Saving the request directly

If you want to use `$post->update(request()->all())` you can structure your input in semicolon notation.

```html
<input type="text" name="title:en" />
<input type="text" name="title:de" />
```

```php
public function update(Request $request, Post $post)
{
  $post->update($request->all());
}
```
# Updating the default locale

You can update the default locale attributes by accessing the translatable attributes from the model itself.

```php
$post->title = 'My first post'; // Will update the English translatable model
```

