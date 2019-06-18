# FAQ

## **Do you have any example code?**

Examples for all the package features can be found [in the code](https://github.com/Astrotomic/laravel-translatable/tree/master/tests/models) used for the [tests](https://github.com/Astrotomic/laravel-translatable/tree/master/tests).

## **Can I ask you some questions?**

Got any question or suggestion? Feel free to open an [Issue](https://github.com/Astrotomic/laravel-translatable/issues/new).

## **Is there anything I can help you with?**

You are awesome! Watch the repo and reply to the issues. You will help offering a great experience to the users of the package. `#communityWorks`

## How to fix collisions with other traits/methods?

Translatable is fully compatible with all kinds of Eloquent extensions, including Ardent. If you need help to implement Translatable with these extensions, see this [example](https://gist.github.com/dimsav/9659552).

## **How do I migrate my existing table to use laravel-translatable?**

Please see the installation steps to understand how your database should be structured.

If your properties are written in english, we recommend using these commands in your migrations:

```php
// We insert the old attributes into the fresh translation table: 
\DB::statement("insert into country_translations (country_id, name, locale) select id, name, 'en' from countries");

// We drop the translation attributes in our main table: 
Schema::table('posts', function ($table) {
    $table->dropColumn('title');
    $table->dropColumn('content');
});
```

## **How do I sort by translations?**

We provide a [scope](https://github.com/Astrotomic/laravel-translatable/blob/826fb909eb81f80cccc947a7b66cb9ef35a6e5ef/src/Translatable/Translatable.php#L448-L464) to order the main model entries by it's translation values.

## How can I select a model by translated field?

For example, let's image we want to find the `Post` having a `PostTranslation` title equal to `My first post`.

```php
Post::whereHas('translations', function ($query) {
    $query
        ->where('locale', 'en')
        ->where('title', 'My first post');
})->first();
```

You can find more info at the Laravel [Querying Relations docs](http://laravel.com/docs/5.1/eloquent-relationships#querying-relations). But we also provide [several scopes](https://github.com/Astrotomic/laravel-translatable/blob/826fb909eb81f80cccc947a7b66cb9ef35a6e5ef/src/Translatable/Translatable.php#L408-L446) to cover the most common scenarios.

## **Why do I get a mysql error while running the migrations?**

If you see the following mysql error:

```text
[Illuminate\Database\QueryException]
SQLSTATE[HY000]: General error: 1005 Can't create table 'my_database.#sql-455_63'
  (errno: 150) (SQL: alter table `country_translations` 
  add constraint country_translations_country_id_foreign foreign key (`country_id`) 
  references `countries` (`id`) on delete cascade)
```

Then your tables have the MyISAM engine which doesn't allow foreign key constraints. MyISAM was the default engine for mysql versions older than 5.5. Since [version 5.5](http://dev.mysql.com/doc/refman/5.5/en/innodb-default-se.html), tables are created using the InnoDB storage engine by default.

### **How to fix**

For tables already created in production, update your migrations to change the engine of the table before adding the foreign key constraint.

```php
public function up()
{
    DB::statement('ALTER TABLE countries ENGINE=InnoDB');
}

public function down()
{
    DB::statement('ALTER TABLE countries ENGINE=MyISAM');
}
```

For new tables, a quick solution is to set the storage engine in the migration:

```php
Schema::create('language_translations', function(Blueprint $table){
  $table->engine = 'InnoDB';
  $table->increments('id');
    // ...
});
```

The best solution though would be to update your mysql version. And **always make sure you have the same version both in development and production environment!**

