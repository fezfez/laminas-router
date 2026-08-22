<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\AssembledUrl;
use Laminas\Router\Exception;
use Laminas\Router\RouteMatchInterface;
use Laminas\Router\RoutePluginManager;
use Laminas\Translator\TranslatorInterface;
use Override;
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
        RoutePluginManager $routePluginManager,
        private TranslatorInterface $translator,
        array $routes = [],
        array $defaultParams = [],
        int|null $priority = null,
        private string $translatorTextDomain = 'default'
    ) {
        parent::__construct($routePluginManager, $routes, $defaultParams, $priority);
    }

    /**
     * @inheritDoc
     * @param int|null $pathOffset
     */
    #[Override]
    public function match(
        RequestInterface $request,
        int|null $pathOffset = null,
        array $options = []
    ): ?RouteMatchInterface {
        if (! isset($options['translator'])) {
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
    #[Override]
    public function assemble(array $params = [], array $options = []): AssembledUrl
    {
        if (! isset($options['translator'])) {
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

    public function getTranslatorTextDomain(): string
    {
        return $this->translatorTextDomain;
    }
}
