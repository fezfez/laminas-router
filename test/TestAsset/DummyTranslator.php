<?php

declare(strict_types=1);

namespace LaminasTest\Router\TestAsset;

use Laminas\Translator\TranslatorInterface;

/**
 * No-op translator for assembling a test RouteBuilderRegistry.
 */
final class DummyTranslator implements TranslatorInterface
{
    public function translate(
        string $message,
        string $textDomain = self::DEFAULT_TEXT_DOMAIN,
        ?string $locale = null,
    ): string {
        return $message;
    }

    public function translatePlural(
        string $singular,
        string $plural,
        int $number,
        string $textDomain = self::DEFAULT_TEXT_DOMAIN,
        ?string $locale = null
    ): string {
        return $number === 1 ? $singular : $plural;
    }
}
