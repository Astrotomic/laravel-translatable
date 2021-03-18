# Installation

### Install package

Add the package in your `composer.json` by executing the command.

```bash
composer require astrotomic/laravel-translatable
```

### Configuration

We copy the configuration file to our project.

```bash
php artisan vendor:publish --provider="Astrotomic\Translatable\TranslatableServiceProvider" 
```

{% hint style="info" %}
There isn't any restriction for the format of the locales. Feel free to use whatever suits you better, like "eng" instead of "en", or "el" instead of "gr". The important is to define your locales and stick to them.
{% endhint %}

### Migrations

In this example, we want to translate the model `Post`. We will need an extra table `post_translations`:

{% code-tabs %}
{% code-tabs-item title="create\_posts\_table.php" %}
```php
Schema::create('posts', function(Blueprint $table) {
    $table->increments('id');
    $table->string('author');
    $table->timestamps();
});
```
{% endcode-tabs-item %}
{% endcode-tabs %}

{% code-tabs %}
{% code-tabs-item title="create\_post\_translations\_table" %}
```php
Schema::create('post_translations', function(Blueprint $table) {
    $table->increments('id');
    $table->integer('post_id')->unsigned();
    $table->string('locale')->index();
    $table->string('title');
    $table->text('content');

    $table->unique(['post_id', 'locale']);
    $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
});
```
{% endcode-tabs-item %}
{% endcode-tabs %}

### Models

The translatable model `Post` should [use the trait](http://www.sitepoint.com/using-traits-in-php-5-4/) `Astrotomic\Translatable\Translatable`. The default convention for the translation model is `PostTranslation`. The array `$translatedAttributes` contains the names of the fields being translated in the `PostTranslation` model.

{% code-tabs %}
{% code-tabs-item title="Post.php" %}
```php
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Post extends Model implements TranslatableContract
{
    use Translatable;
    
    public $translatedAttributes = ['title', 'content'];
    protected $fillable = ['author'];
}
```
{% endcode-tabs-item %}
{% endcode-tabs %}

{% code-tabs %}
{% code-tabs-item title="PostTranslation.php" %}
```php
class PostTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['title', 'content'];
}
```
{% endcode-tabs-item %}
{% endcode-tabs %}

You may also define a custom foreign key for the package to use, e.g. in case of single table inheritance. 
So, you have a child class `ChildPost` that inherits from `Post` class, but has the same database table as its parent.

{% code-tabs %}
{% code-tabs-item title="ChildPost.php" %}
```php

class ChildPost extends Post 
{
    protected $table = 'posts';
    
    /*
     * ... some extra attributes or function ...
     */
}
```
{% endcode-tabs-item %}
{% endcode-tabs %}

You will have to create a Translation Class for it.

{% code-tabs %}
{% code-tabs-item title="ChildPostTranslation.php" %}
```php

use Illuminate\Database\Eloquent\Model;
class ChildPostTranslation extends Model 
{
    protected $table = 'post_translations';
    public $timestamps = false;
    protected $fillable = ['title', 'content'];
    
}
```
{% endcode-tabs-item %}
{% endcode-tabs %}

This will try to get data from `post_translations` table using foreign key `child_post_id` according to Laravel.
So, in this case, you will have to change the property `$translationForeignKey` to your `'post_id'`
{% code-tabs %}
{% code-tabs-item title="Post.php" %}
```php
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Post extends Model implements TranslatableContract
{
    use Translatable;
    
    public $translatedAttributes = ['title', 'content'];
    protected $fillable = ['author'];
    // will be used first in case of custom foreign key name, or
    // in case of single table inheritance, children classes will use it 
    // instead of using the naming convention which will not work
    public $trasnlationForeignKey = 'post_id';
}
```
{% endcode-tabs-item %}
{% endcode-tabs %}

Another case to define a custom foreign key for the package to use, is when your table has a custom foreign key that is not derived from the table name.
Going with the example above, it may be something like this
{% code-tabs %}
{% code-tabs-item title="create\_post\_translations\_table" %}
```php
Schema::create('post_translations', function(Blueprint $table) {
    // ...
    // other columns
    // ...
    
    
    
    $table->unique(['custom_post_id', 'locale']);
    $table->foreign('custom_post_id')->references('id')->on('posts')->onDelete('cascade');
});
```
{% endcode-tabs-item %}
{% endcode-tabs %}
In this case also, you will have to change the property `$translationForeignKey` to your `'custom_post_id'`
{% code-tabs %}
{% code-tabs-item title="Post.php" %}
```php
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Post extends Model implements TranslatableContract
{
    use Translatable;
    
    public $translatedAttributes = ['title', 'content'];
    protected $fillable = ['author'];
    // will be used first in case of custom foreign key name, or
    // in case of single table inheritance, children classes will use it 
    // instead of using the naming convention which will not work
    public $trasnlationForeignKey = 'custom_post_id';
}
```
{% endcode-tabs-item %}
{% endcode-tabs %}
