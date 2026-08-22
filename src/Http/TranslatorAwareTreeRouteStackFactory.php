<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\RoutePluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Translator\TranslatorInterface;
use Psr\Container\ContainerInterface;

use function assert;

final readonly class TranslatorAwareTreeRouteStackFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): TranslatorAwareTreeRouteStack {
        $options = $options ?? [];
        $routePlugins = $container->get(RoutePluginManager::class);
        assert($routePlugins instanceof RoutePluginManager);
        $translator = $options['translator'] ?? $container->get(TranslatorInterface::class);
        return new TranslatorAwareTreeRouteStack(
            $routePlugins,
            $translator,
            $options['routes'] ?? [],
            $options['default_params'] ?? [],
            $options['priority'] ?? null,
            $options['translator_text_domain'] ?? TranslatorInterface::DEFAULT_TEXT_DOMAIN,
        );
    }
}
