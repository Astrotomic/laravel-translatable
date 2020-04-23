<?php

namespace Astrotomic\Translatable\TranslationResolvers;

use Astrotomic\Translatable\Translatable;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ConfigFallbackLocale extends BaseFallbackResolver
{
    /** @var Config */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    protected function fallbackLocales(string $locale): array
    {
        return [$this->config->get('translatable.fallback_locale')];
    }
}
