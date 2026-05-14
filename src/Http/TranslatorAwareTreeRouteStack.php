<?php

declare(strict_types=1);

namespace Laminas\Router\Http;

use Laminas\Router\Exception;
use Laminas\Router\PriorityList;
use Laminas\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Laminas\Router\RouteStackInterface;
use Laminas\Stdlib\RequestInterface;
use Laminas\Translator\TranslatorInterface;
use Laminas\Uri\Http as HttpUri;

use function assert;

/**
 * Translator aware tree route stack.
 *
 * @template TRoute of HttpRouteInterface
 * @template-implement RouteStackInterface<TRoute>
 */
final class TranslatorAwareTreeRouteStack implements RouteStackInterface
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

    private readonly TreeRouteStack $treeRouteStack;

    public function __construct(
        TreeRouteStack $treeRouteStack,
    ) {
        $this->treeRouteStack = $treeRouteStack;
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     */
    public static function factory(array $options = []): self
    {
        return new self(TreeRouteStack::factory($options));
    }

    /** @inheritDoc */
    public function addRoutes(array $routes): void
    {
        $this->treeRouteStack->addRoutes($routes);
    }

    /** @inheritDoc */
    public function addRoute(string|int $name, int|string|array|RouteInterface $route, ?int $priority = null): void
    {
        $this->treeRouteStack->addRoute($name, $route, $priority);
    }

    /** @inheritDoc */
    public function removeRoute(string $name): void
    {
        $this->treeRouteStack->removeRoute($name);
    }

    /** @inheritDoc */
    public function setRoutes(array $routes): void
    {
        $this->treeRouteStack->setRoutes($routes);
    }

    public function getRoutes(): PriorityList
    {
        return $this->treeRouteStack->getRoutes();
    }

    /**
     * @param non-empty-string $name
     */
    public function hasRoute(string $name): bool
    {
        return $this->treeRouteStack->hasRoute($name);
    }

    /**
     * @param non-empty-string $name
     * @return HttpRouteInterface|null the route
     */
    public function getRoute(string $name): HttpRouteInterface|null
    {
        $route = $this->treeRouteStack->getRoute($name);
        assert($route === null || $route instanceof HttpRouteInterface);

        return $route;
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $value
     */
    public function setDefaultParam(string $name, string $value): void
    {
        $this->treeRouteStack->setDefaultParam($name, $value);
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->treeRouteStack->setBaseUrl($baseUrl);
    }

    public function getBaseUrl(): ?string
    {
        return $this->treeRouteStack->getBaseUrl();
    }

    public function setRequestUri(HttpUri $uri): void
    {
        $this->treeRouteStack->setRequestUri($uri);
    }

    public function getRequestUri(): ?HttpUri
    {
        return $this->treeRouteStack->getRequestUri();
    }

    /**
     * @inheritDoc
     * @param int|null $pathOffset
     */
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

        return $this->treeRouteStack->match($request, $pathOffset, $options);
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function assemble(array $params = [], array $options = []): string
    {
        if ($this->hasTranslator() && $this->isTranslatorEnabled() && ! isset($options['translator'])) {
            $options['translator'] = $this->getTranslator();
        }

        if (! isset($options['text_domain'])) {
            $options['text_domain'] = $this->getTranslatorTextDomain();
        }

        return $this->treeRouteStack->assemble($params, $options);
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
