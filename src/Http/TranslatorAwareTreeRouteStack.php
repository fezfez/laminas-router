<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use ArrayObject;
use Laminas\Router\Exception;
use Laminas\Router\PriorityList;
use Laminas\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Laminas\Router\RoutePluginManager;
use Laminas\Stdlib\RequestInterface;
use Laminas\Translator\TranslatorInterface;
use Laminas\Uri\Http as HttpUri;
use Override;

/**
 * Translator aware tree route stack (composition around {@see TreeRouteStack}).
 *
 * @template TRoute of HttpRouteInterface
 */
final class TranslatorAwareTreeRouteStack implements HttpNestedRoutesCapableInterface
{
    /**
     * @internal
     * @deprecated Since 3.9.0 This property will be removed or made private in version 4.0
     */
    public int|null $priority = null;

    /**
     * Translator used for translatable segments.
     */
    private ?TranslatorInterface $translator = null;

    /**
     * Whether the translator is enabled.
     */
    private bool $translatorEnabled = true;

    /**
     * Translator text domain to use.
     */
    private string $translatorTextDomain = 'default';

    private readonly TreeRouteStack $inner;

    /**
     * @param ArrayObject<string, TRoute> $prototypes
     * @param array<non-empty-string, array|TRoute> $routes
     * @param array<non-empty-string, non-empty-string> $defaultParams
     */
    public function __construct(
        RoutePluginManager|TreeRouteStack $routePluginManagerOrInner,
        ArrayObject|null $prototypes = null,
        array $routes = [],
        array $defaultParams = []
    ) {
        if ($routePluginManagerOrInner instanceof TreeRouteStack) {
            $this->inner = $routePluginManagerOrInner;

            return;
        }

        $this->inner = new TreeRouteStack(
            $routePluginManagerOrInner,
            $prototypes ?? new ArrayObject(),
            $routes,
            $defaultParams
        );
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     */
    #[Override]
    public static function factory(array $options = []): static
    {
        return new self(TreeRouteStack::factory($options));
    }

    /** @inheritDoc */
    #[Override]
    public function addRoutes(array $routes): void
    {
        $this->inner->addRoutes($routes);
    }

    /** @inheritDoc */
    #[Override]
    public function addRoute(string|int $name, int|string|array|RouteInterface $route, ?int $priority = null): void
    {
        $this->inner->addRoute($name, $route, $priority);
    }

    /** @inheritDoc */
    #[Override]
    public function removeRoute(string $name): void
    {
        $this->inner->removeRoute($name);
    }

    /** @inheritDoc */
    #[Override]
    public function setRoutes(array $routes): void
    {
        $this->inner->setRoutes($routes);
    }

    public function getRoutes(): PriorityList
    {
        return $this->inner->getRoutes();
    }

    /**
     * @param non-empty-string $name
     */
    public function hasRoute(string $name): bool
    {
        return $this->inner->hasRoute($name);
    }

    /**
     * @param non-empty-string $name
     * @return TRoute|null the route
     */
    public function getRoute(string $name): RouteInterface|null
    {
        return $this->inner->getRoute($name);
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $value
     */
    public function setDefaultParam(string $name, string $value): void
    {
        $this->inner->setDefaultParam($name, $value);
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->inner->setBaseUrl($baseUrl);
    }

    public function getBaseUrl(): ?string
    {
        return $this->inner->getBaseUrl();
    }

    public function setRequestUri(HttpUri $uri): void
    {
        $this->inner->setRequestUri($uri);
    }

    public function getRequestUri(): ?HttpUri
    {
        return $this->inner->getRequestUri();
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
    ): ?RouteMatch {
        if ($this->hasTranslator() && $this->isTranslatorEnabled() && ! isset($options['translator'])) {
            $options['translator'] = $this->getTranslator();
        }

        if (! isset($options['text_domain'])) {
            $options['text_domain'] = $this->getTranslatorTextDomain();
        }

        return $this->inner->match($request, $pathOffset, $options);
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    #[Override]
    public function assemble(array $params = [], array $options = []): string
    {
        if ($this->hasTranslator() && $this->isTranslatorEnabled() && ! isset($options['translator'])) {
            $options['translator'] = $this->getTranslator();
        }

        if (! isset($options['text_domain'])) {
            $options['text_domain'] = $this->getTranslatorTextDomain();
        }

        return $this->inner->assemble($params, $options);
    }

    public function setTranslator(?TranslatorInterface $translator = null, ?string $textDomain = null): self
    {
        $this->translator = $translator;

        if ($textDomain !== null) {
            $this->setTranslatorTextDomain($textDomain);
        }

        return $this;
    }

    public function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }

    public function hasTranslator(): bool
    {
        return $this->translator !== null;
    }

    public function setTranslatorEnabled(bool $enabled = true): self
    {
        $this->translatorEnabled = $enabled;
        return $this;
    }

    public function isTranslatorEnabled(): bool
    {
        return $this->translatorEnabled;
    }

    public function setTranslatorTextDomain(string $textDomain = 'default'): self
    {
        $this->translatorTextDomain = $textDomain;

        return $this;
    }

    public function getTranslatorTextDomain(): string
    {
        return $this->translatorTextDomain;
    }
}
