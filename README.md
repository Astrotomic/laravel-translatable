# Introduction

[![Latest Version](http://img.shields.io/packagist/v/astrotomic/laravel-translatable.svg?label=Release&style=for-the-badge)](https://packagist.org/packages/astrotomic/laravel-translatable)
[![MIT License](https://img.shields.io/github/license/Astrotomic/laravel-translatable.svg?label=License&color=blue&style=for-the-badge)](https://github.com/Astrotomic/laravel-translatable/blob/master/LICENSE)
[![Offset Earth](https://img.shields.io/badge/Treeware-%F0%9F%8C%B3-green?style=for-the-badge)](https://plant.treeware.earth/Astrotomic/laravel-translatable)
[![Larabelles](https://img.shields.io/badge/Larabelles-%F0%9F%A6%84-lightpink?style=for-the-badge)](https://www.larabelles.com/)

[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/Astrotomic/laravel-translatable/phpunit.yml?style=flat-square&logoColor=white&logo=github&label=PHPunit)](https://github.com/Astrotomic/laravel-translatable/actions?query=workflow%3Aphpunit)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/Astrotomic/laravel-translatable/pint.yml?style=flat-square&logoColor=white&logo=github&label=Pint)](https://github.com/Astrotomic/laravel-translatable/actions?query=workflow%3Apint)
[![Codecov Coverage](https://img.shields.io/codecov/c/github/Astrotomic/laravel-translatable?logo=codecov&logoColor=white&label=Codecov&style=flat-square)](https://codecov.io/gh/Astrotomic/laravel-translatable)
[![Total Downloads](https://img.shields.io/packagist/dt/astrotomic/laravel-translatable.svg?label=Downloads&style=flat-square)](https://packagist.org/packages/astrotomic/laravel-translatable)
[![GitBook](https://img.shields.io/badge/GitBook-Astrotomic-7e57c2.svg?style=flat-square)](https://docs.astrotomic.info/laravel-translatable)

<p align="center">
    <img src="/art/socialcard.png" alt="laravel-translatable socialcard">
</p>

**If you want to store translations of your models into the database, this package is for you.**

This is a Laravel package for translatable models. Its goal is to remove the complexity in retrieving and storing multilingual model instances. With this package you write less code, as the translations are being fetched/saved when you fetch/save your instance.

The full documentation can be found at [GitBook](https://docs.astrotomic.info/laravel-translatable).

## Installation

```bash
composer require astrotomic/laravel-translatable
```

## Quick Example

### **Getting translated attributes**

```php
$post = Post::first();
echo $post->translate('en')->title; // My first post

App::setLocale('en');
echo $post->title; // My first post

App::setLocale('de');
echo $post->title; // Mein erster Post
```

### **Saving translated attributes**

```php
$post = Post::first();
echo $post->translate('en')->title; // My first post

$post->translate('en')->title = 'My cool post';
$post->save();

$post = Post::first();
echo $post->translate('en')->title; // My cool post
```

### **Filling multiple translations**

```php
$data = [
  'author' => 'Gummibeer',
  'en' => ['title' => 'My first post'],
  'fr' => ['title' => 'Mon premier post'],
];
$post = Post::create($data);

echo $post->translate('fr')->title; // Mon premier post
```

#### Filling multiple translations wrapped

You may define a wrapper property when creating new translations. Set the `translations_wrapper` property in translatable config file:

```php
'translations_wrapper' => 'translations',
```

Then just wrap multiple locales using that property:

```php
$data = [
  'author' => 'Gummibeer',
  'translations' => [
      'en' => ['title' => 'My first post'],
      'fr' => ['title' => 'Mon premier post'],
  ],
];
$post = Post::create($data);

echo $post->translate('fr')->title; // Mon premier post
```

## Tutorials

- [How To Add Multilingual Support to Eloquent](https://laravel-news.com/how-to-add-multilingual-support-to-eloquent)
- [How To Build An Efficient and SEO Friendly Multilingual Architecture For Your Laravel Application](https://mydnic.be/post/how-to-build-an-efficient-and-seo-friendly-multilingual-architecture-for-your-laravel-application)
- [How to Add Multi-Language Models to Laravel QuickAdminPanel](https://quickadminpanel.com/blog/how-to-add-multi-language-models-to-laravel-quickadminpanel/)

## Changelog

Please see [CHANGELOG](docs/changelog.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/Astrotomic/.github/blob/master/CONTRIBUTING.md) for details. You could also be interested in [CODE OF CONDUCT](https://github.com/Astrotomic/.github/blob/master/CODE_OF_CONDUCT.md).

### Security

If you discover any security related issues, please check [SECURITY](https://github.com/Astrotomic/.github/blob/master/SECURITY.md) for steps to report it.

## Credits

- [Tom Witkowski](https://github.com/Gummibeer) _current maintainer_
- [Dimitrios Savvopoulos](https://github.com/dimsav) _original author_
- [David Llop](https://github.com/Lloople) _refactoring testsuite_
- [Caneco](https://github.com/caneco) _artwork_
- [All Contributors](../../contributors)

## Versions

| Package             | Laravel                       | PHP       |
| :------------------ | :---------------------------- | :-------- |
| **v11.13 - v11.15** | `9.* / 10.* / 11.*`           | `^8.0`    |
| **v11.12 - v11.12** | `8.* / 9.* / 10.*`            | `^8.0`    |
| **v11.10 - v11.11** | `8.* / 9.*`                   | `^8.0`    |
| **v11.6 - v11.9**   | `5.8.* / 6.* / 7.* / 8.*`     | `>=7.2`   |
| **v11.4 - v11.5**   | `5.6.* / 5.7.* / 5.8.* / 6.*` | `>=7.1.3` |
| **v11.0 - v11.3**   | `5.6.* / 5.7.* / 5.8.*`       | `>=7.1.3` |

## Treeware

You're free to use this package, but if it makes it to your production environment I would highly appreciate you buying the world a tree.

It’s now common knowledge that one of the best tools to tackle the climate crisis and keep our temperatures from rising above 1.5C is to [plant trees](https://www.bbc.co.uk/news/science-environment-48870920). If you contribute to my forest you’ll be creating employment for local families and restoring wildlife habitats.

You can buy trees at [offset.earth/treeware](https://plant.treeware.earth/Astrotomic/laravel-translatable)

Read more about Treeware at [treeware.earth](https://treeware.earth)
