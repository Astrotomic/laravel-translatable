<?php

use Astrotomic\Translatable\Locales;
use Astrotomic\Translatable\Validation\RuleFactory;

class ValidationTest extends TestsBase
{
    public function test_it_does_not_touch_untranslated_keys()
    {
        $rules = [
            'title' => 'required',
            'author_id' => [
                'required',
                'int',
            ],
        ];

        $this->assertEquals($rules, RuleFactory::make($rules));
    }

    public function test_format_array_it_replaces_single_key()
    {
        $rules = [
            'title' => 'required',
            '%content%' => 'required',
        ];

        $this->assertEquals([
            'title' => 'required',
            'en.content' => 'required',
            'de.content' => 'required',
            'de-DE.content' => 'required',
            'de-AT.content' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY));
    }

    public function test_format_array_it_replaces_sub_key()
    {
        $rules = [
            'title' => 'required',
            'translations.%content%' => 'required',
        ];

        $this->assertEquals([
            'title' => 'required',
            'translations.en.content' => 'required',
            'translations.de.content' => 'required',
            'translations.de-DE.content' => 'required',
            'translations.de-AT.content' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY));
    }

    public function test_format_array_it_replaces_middle_key()
    {
        $rules = [
            'title' => 'required',
            'translations.%content%.body' => 'required',
        ];

        $this->assertEquals([
            'title' => 'required',
            'translations.en.content.body' => 'required',
            'translations.de.content.body' => 'required',
            'translations.de-DE.content.body' => 'required',
            'translations.de-AT.content.body' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY));
    }

    public function test_format_array_it_replaces_middle_key_with_custom_prefix()
    {
        $rules = [
            'title' => 'required',
            'translations.{content%.body' => 'required',
        ];

        $this->assertEquals([
            'title' => 'required',
            'translations.en.content.body' => 'required',
            'translations.de.content.body' => 'required',
            'translations.de-DE.content.body' => 'required',
            'translations.de-AT.content.body' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY, '{'));
    }

    public function test_format_array_it_replaces_middle_key_with_custom_suffix()
    {
        $rules = [
            'title' => 'required',
            'translations.%content}.body' => 'required',
        ];

        $this->assertEquals([
            'title' => 'required',
            'translations.en.content.body' => 'required',
            'translations.de.content.body' => 'required',
            'translations.de-DE.content.body' => 'required',
            'translations.de-AT.content.body' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY, '%', '}'));
    }

    public function test_format_array_it_replaces_middle_key_with_custom_delimiters()
    {
        $rules = [
            'title' => 'required',
            'translations.{content}.body' => 'required',
        ];

        $this->assertEquals([
            'title' => 'required',
            'translations.en.content.body' => 'required',
            'translations.de.content.body' => 'required',
            'translations.de-DE.content.body' => 'required',
            'translations.de-AT.content.body' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY, '{', '}'));
    }

    public function test_format_array_it_replaces_middle_key_with_custom_regex_delimiters()
    {
        $rules = [
            'title' => 'required',
            'translations.$content$.body' => 'required',
        ];

        $this->assertEquals([
            'title' => 'required',
            'translations.en.content.body' => 'required',
            'translations.de.content.body' => 'required',
            'translations.de-DE.content.body' => 'required',
            'translations.de-AT.content.body' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY, '$', '$'));
    }

    public function test_format_array_it_uses_config_as_default()
    {
        app('config')->set('translatable.rule_factory', [
            'format' => RuleFactory::FORMAT_ARRAY,
            'prefix' => '{',
            'suffix' => '}',
        ]);

        $rules = [
            'title' => 'required',
            '{content}' => 'required',
            '%content%' => 'required',
        ];

        $this->assertEquals([
            'title' => 'required',
            '%content%' => 'required',
            'en.content' => 'required',
            'de.content' => 'required',
            'de-DE.content' => 'required',
            'de-AT.content' => 'required',
        ], RuleFactory::make($rules));
    }

    public function test_format_key_it_replaces_single_key()
    {
        $rules = [
            'title' => 'required',
            '%content%' => 'required',
        ];

        $this->assertEquals([
            'title' => 'required',
            'content:en' => 'required',
            'content:de' => 'required',
            'content:de-DE' => 'required',
            'content:de-AT' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_KEY));
    }

    public function test_format_key_it_replaces_sub_key()
    {
        $rules = [
            'title' => 'required',
            'translations.%content%' => 'required',
        ];

        $this->assertEquals([
            'title' => 'required',
            'translations.content:en' => 'required',
            'translations.content:de' => 'required',
            'translations.content:de-DE' => 'required',
            'translations.content:de-AT' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_KEY));
    }

    public function test_format_key_it_replaces_middle_key()
    {
        $rules = [
            'title' => 'required',
            'translations.%content%.body' => 'required',
        ];

        $this->assertEquals([
            'title' => 'required',
            'translations.content:en.body' => 'required',
            'translations.content:de.body' => 'required',
            'translations.content:de-DE.body' => 'required',
            'translations.content:de-AT.body' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_KEY));
    }

    public function test_format_key_it_uses_config_as_default()
    {
        app('config')->set('translatable.rule_factory', [
            'format' => RuleFactory::FORMAT_KEY,
            'prefix' => '{',
            'suffix' => '}',
        ]);

        $rules = [
            'title' => 'required',
            '{content}' => 'required',
            '%content%' => 'required',
        ];

        $this->assertEquals([
            'title' => 'required',
            '%content%' => 'required',
            'content:en' => 'required',
            'content:de' => 'required',
            'content:de-DE' => 'required',
            'content:de-AT' => 'required',
        ], RuleFactory::make($rules));
    }

    public function test_it_replaces_key_with_custom_locales()
    {
        $rules = [
            'title' => 'required',
            'translations.%content%.body' => 'required',
        ];

        $this->assertEquals([
            'title' => 'required',
            'translations.en.content.body' => 'required',
            'translations.de.content.body' => 'required',
        ], RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY, '%', '%', [
            'en',
            'de',
        ]));
    }

    public function test_it_throws_exception_with_undefined_locales()
    {
        $this->expectException(InvalidArgumentException::class);

        $rules = [
            'title' => 'required',
            'translations.$content$.body' => 'required',
        ];

        RuleFactory::make($rules, RuleFactory::FORMAT_ARRAY, '%', '%', [
            'en',
            'de',
            'at',
        ]);
    }

    protected function setUp(): void
    {
        $this->setUpTheTestEnvironment();
        app('config')->set('translatable.locales', [
            'en',
            'de' => [
                'DE',
                'AT',
            ],
        ]);
        $this->getLocalesHelper()->load();
    }

    private function getLocalesHelper(): Locales
    {
        return app(Locales::class);
    }
}
