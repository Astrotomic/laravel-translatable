# Installation

## Install package

Add the package in your `composer.json` by executing the command.

```bash
composer require astrotomic/laravel-translatable
```

## Configuration

We copy the configuration file to our project.

```bash
php artisan vendor:publish --tag=translatable
```

After this you will have to configure the `locales` your app should use.

```php
'locales' => [
    'en',
    'fr',
    'es' => [
        'MX', // mexican spanish
        'CO', // colombian spanish
    ],
],
```

{% hint style="info" %}
There isn't any restriction for the format of the locales. Feel free to use whatever suits you better, like "eng" instead of "en", or "el" instead of "gr". The important is to define your locales and stick to them.
{% endhint %}

That's the only configuration key you **have** to adjust. All the others have a working default value and are described in the configuration file itself.

## Migrations

In this example, we want to translate the model `Post`. We will need an extra table `post_translations`:

{% code title="create:posts:table.php" %}
```php
Schema::create('posts', function(Blueprint $table) {
    $table->increments('id');
    $table->string('author');
    $table->timestamps();
});
```
{% endcode %}

{% code title="create:post:translations:table" %}
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
{% endcode %}

## Models

The translatable model `Post` should [use the trait](http://www.sitepoint.com/using-traits-in-php-5-4/) `Astrotomic\Translatable\Translatable`. The default convention for the translation model is `PostTranslation`. The array `$translatedAttributes` contains the names of the fields being translated in the `PostTranslation` model.

{% code title="Post.php" %}
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
{% endcode %}

{% code title="PostTranslation.php" %}
```php
class PostTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['title', 'content'];
}
```
{% endcode %}

### Custom foreign key

You may also define a custom foreign key for the package to use, e.g. in case of single table inheritance. So, you have a child class `ChildPost` that inherits from `Post` class, but has the same database table as its parent.

{% code title="ChildPost.php" %}
```php
class ChildPost extends Post
{
    protected $table = 'posts';
}
```
{% endcode %}

You will have to create a Translation Class for it.

{% code title="ChildPostTranslation.php" %}
```php
use Illuminate\Database\Eloquent\Model;

class ChildPostTranslation extends Model
{
    protected $table = 'post_translations';
    public $timestamps = false;
    protected $fillable = ['title', 'content'];
}
```
{% endcode %}

This will try to get data from `post_translations` table using foreign key `child_post_id` according to Laravel. So, in this case, you will have to change the property `$translationForeignKey` to your `'post_id'`.

{% code title="ChildPost.php" %}
```php
class ChildPost extends Post
{
    protected $table = 'posts';
    protected $translationForeignKey = 'post_id';
}
```
{% endcode %}

### Custom locale key

The package uses a database field to define the locale parameter in the translation model. By default, the name of this field is `locale`, but you can change it either globally by adjusting the value of the `locale_key` entry inside the config file or you can change it on a per-model basis by defining the property `$localeKey` on the translatable model. In the following example, we're setting this value to be `language`.&#x20;

{% code title="Post.php" %}
```php
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Post extends Model implements TranslatableContract
{
    use Translatable;

    public $translatedAttributes = ['title', 'content'];
    protected $fillable = ['author'];
    protected $localeKey = 'language';
}
```
{% endcode %}

Also, don't forget to update your translations table migration file:

{% code title="create:post:translations:table" %}
```php
Schema::create('post_translations', function(Blueprint $table) {
    $table->increments('id');
    $table->integer('post_id')->unsigned();
    $table->string('language')->index();
    $table->string('title');
    $table->text('content');

    $table->unique(['post_id', 'language']);
    $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
});
```
{% endcode %}

### Custom translation model

As said earlier, the package depends on a convention to determine the translation model by taking the model name and appending a suffix to it, this suffix is read from the `translation_suffix` key within the config file, and by default this suffix contains the value `Translation`. For instance, if you have a `Post` model, the corresponding translation model will be called `PostTranslation`.

However, you may want to use a different translation model name that doesn't follow this convention. In this case, you may define the `$translationModel` property on the translatable model.

{% code title="Post.php" %}
```php
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Post extends Model implements TranslatableContract
{
    use Translatable;

    public $translatedAttributes = ['title', 'content'];
    protected $fillable = ['author'];
    protected $translationModel = App\Models\MainPostTrans::class;
}
```
{% endcode %}

