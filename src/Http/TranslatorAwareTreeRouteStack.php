<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\AssembledUrl;
use Laminas\Router\Exception;
use Laminas\Router\RouteBuilderRegistry;
use Laminas\Router\RouteMatchInterface;
use Laminas\Translator\TranslatorInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Translator aware tree route stack.
 *
 * @template TRoute of HttpRouteInterface
 * @template-extends TreeRouteStack<TRoute>
 */
final readonly class TranslatorAwareTreeRouteStack extends TreeRouteStack
{
    /**
     * @param array<non-empty-string|array-key, array|TRoute> $routes
     * @param array<string, string|int|float|null> $defaultParams
     */
    public function __construct(
        RouteBuilderRegistry $routeBuilderRegistry,
        array $routes = [],
        array $defaultParams = [],
        int|null $priority = null,
        private ?TranslatorInterface $translator = null,
        private string $translatorTextDomain = 'default'
    ) {
        parent::__construct($routeBuilderRegistry, $routes, $defaultParams, $priority);
    }

    /**
     * @inheritDoc
     * @param int|null $pathOffset
     */
    public function match(
        RequestInterface $request,
        int|null $pathOffset = null,
        array $options = []
    ): ?RouteMatchInterface {
        if ($this->isTranslatorEnabled() && ! isset($options['translator'])) {
            $options['translator'] = $this->getTranslator();
        }

        if (! isset($options['text_domain'])) {
            $options['text_domain'] = $this->getTranslatorTextDomain();
        }

        return parent::match($request, $pathOffset, $options);
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function assemble(array $params = [], array $options = []): AssembledUrl
    {
        if ($this->isTranslatorEnabled() && ! isset($options['translator'])) {
            $options['translator'] = $this->getTranslator();
        }

        if (! isset($options['text_domain'])) {
            $options['text_domain'] = $this->getTranslatorTextDomain();
        }

        return parent::assemble($params, $options);
    }

    public function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }

    public function isTranslatorEnabled(): bool
    {
        return $this->translator !== null;
    }

    public function getTranslatorTextDomain(): string
    {
        return $this->translatorTextDomain;
    }
}
