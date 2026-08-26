<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\Exception\RuntimeException;
use Laminas\Router\RouteBuilderInterface;
use Laminas\Router\RouteBuilderRegistry;
use Laminas\Router\RouteInterface;
use Laminas\Translator\TranslatorInterface;

use function is_string;

/**
 * @template TRoute of HttpRouteInterface
 * @implements RouteBuilderInterface<TranslatorAwareTreeRouteStack<TRoute>>
 */
final readonly class TranslatorAwareTreeRouteStackBuilder implements RouteBuilderInterface
{
    public function __construct(
        private RouteBuilderRegistry $routeBuilderRegistry,
        private TranslatorInterface $translator,
    ) {
    }

    public function build(array $options = []): RouteInterface
    {
        /** @psalm-var array<non-empty-string, array|TRoute> $routes */
        $routes = $options['routes'] ?? [];
        /** @psalm-var array<string, string|int|float|null> $defaultParams */
        $defaultParams        = $options['default_params'] ?? [];
        $translatorTextDomain = $options['translator_text_domain'] ?? TranslatorInterface::DEFAULT_TEXT_DOMAIN;
        /** @psalm-var int|null $priority */
        $priority = $options['priority'] ?? null;

        if (! is_string($translatorTextDomain)) {
            throw new RuntimeException('Invalid "translator_text_domain" option');
        }

        return new TranslatorAwareTreeRouteStack(
            $this->routeBuilderRegistry,
            $routes,
            $defaultParams,
            $priority,
            translator: $this->translator,
            translatorTextDomain: $translatorTextDomain
        );
    }
}
