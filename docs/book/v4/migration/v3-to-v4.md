# Migrating from Version 3 to Version 4

Version 4 of laminas-router contains several backward incompatible changes.
This document details those changes and provides guidance on updating your
application.

If you are upgrading from an older 3.x release, ensure you are on **3.18.x**
first. Version 4 requires **PHP 8.2+**, which is already required by 3.18.x.

## Upgrade order

1. Upgrade to PHP 8.2 or later (if not already).
2. Upgrade `laminas/laminas-router` to **^4.0**.
3. Run your test suite and address deprecations reported during the 3.18.x cycle.

If you use `ConfigProvider` with laminas-servicemanager (Mezzio, laminas-mvc),
upgrade `laminas/laminas-servicemanager` to **^4.5** before or alongside the
router upgrade. Service Manager is **not** a runtime dependency of
laminas-router itself.

## Dependency changes

| Package                          | v3                 | v4                                                     |
|----------------------------------|--------------------|--------------------------------------------------------|
| `laminas/laminas-servicemanager` | `^3.14` (required) | **optional** (ConfigProvider / Mezzio / MVC only)      |
| `psr/http-message`               | not required       | `^1.1 \|\| ^2.0` (direct)                              |
| `laminas/laminas-translator`     | not required       | `^1.3 \|\| ^2.0` (direct)                              |
| `laminas/laminas-uri`            | required           | **removed**                                            |

URL assembly no longer uses `Laminas\Uri\Http`. Use the new `AssembledUrl` value
object and PSR-7 `UriInterface` instead (see below).

## New features

### `AssembledUrl` value object

`assemble()` now returns an `AssembledUrl` instance instead of a string. This
object carries the path, query, fragment, host, scheme, port, and the list of
parameter names that were consumed during assembly.

### Immutable routes and stacks

Built-in route classes and route stacks are now `final readonly`. Configuration
must be supplied at construction time via constructors or route builders — the
`factory()` static method has been removed from `RouteInterface`.

### Stricter types

Native parameter, property, and return types have been added throughout the
codebase. We strongly suggest usage of Psalm or PHPStan to benefit from these
improvements.

## Removed classes and features

### `Laminas\Router\Http\Wildcard`

The `Wildcard` route type has been removed. It was deprecated since version 2.3.

**Migration:** Replace `Wildcard` routes with `Segment` routes (or another
supported route type).

### `Laminas\Router\Http\RouteInterface`

This deprecated alias extending `HttpRouteInterface` has been removed.

**Migration:** Replace all references with `Laminas\Router\Http\HttpRouteInterface`.

### `Laminas\Router\Http\RouteMatch`

This deprecated class extending the base `RouteMatch` has been removed. Its
logic is consolidated into `Laminas\Router\Http\HttpRouteMatch`.

**Migration:** Replace all references to `Laminas\Router\Http\RouteMatch` with
`Laminas\Router\Http\HttpRouteMatch`.

**Note:** Where you are consuming or producing "Route Match" instances, prefer a type hint of `Laminas\Router\RouteMatchInterface`.
This interface is implemented by `Laminas\Router\Http\HttpRouteMatch`, and, if you are generating custom route matching results, you must implement this interface to ensure compatibility with version 4.

### `RoutePluginManager`, `RouteInvokableFactory`, and `HttpRouterFactory`

Route creation no longer goes through a plugin manager. The following classes
have been removed:

- `Laminas\Router\RoutePluginManager`
- `Laminas\Router\RoutePluginManagerFactory`
- `Laminas\Router\RouteInvokableFactory`
- `Laminas\Router\Http\HttpRouterFactory`

**Migration:** Use route constructors, `RouteBuilderInterface` implementations,
or `RouteBuilderRegistry::build()` (resolve the registry from the container).
See [Route builders](#route-builders) below.

### Service Manager v2 compatibility

All Service Manager v2 factory methods have been removed from remaining factories:

- `RouterFactory::createService()`

**Migration:** Use PSR-11 / Service Manager v4 `FactoryInterface` with
`__invoke(ContainerInterface, string, ?array)` only.

## Request type: PSR-7

All `match()` methods now accept `Psr\Http\Message\RequestInterface` instead of
`Laminas\Stdlib\RequestInterface`.

```php
// v3
public function match(Laminas\Stdlib\RequestInterface $request);

// v4
public function match(Psr\Http\Message\RequestInterface $request): RouteMatch|null;
```

## `assemble()` returns `AssembledUrl`

```php
// v3
$url = $router->assemble($params, ['name' => 'home']);

// v4
$assembled = $router->assemble($params, ['name' => 'home']);
$url       = $assembled->toString();
$used      = $assembled->assembledParams;
```

HTTP routes that previously exposed `getAssembledParams()` now return that
information via the `AssembledUrl` object. Read `$assembled->assembledParams`
immediately after calling `assemble()`; it is no longer stored on the route
instance.

### Canonical URL assembly

In v3, `TreeRouteStack` maintained internal request URI state via
`setRequestUri()` and used `Laminas\Uri\Http` to build canonical URLs.

In v4, pass a PSR-7 `UriInterface` via the `'uri'` assemble option:

```php
// v3
$router->setRequestUri($request->getUri());
$url = $router->assemble($params, [
    'name'            => 'user',
    'force_canonical' => true,
]);

// v4
$assembled = $router->assemble($params, [
    'name'            => 'user',
    'force_canonical' => true,
    'uri'             => $request->getUri(),
]);
$url = $assembled->toString();
```

The `'normalize_path'` assemble option has been removed.

## Immutability: removed mutable stack APIs

The following methods have been removed from `SimpleRouteStack` and
`TreeRouteStack`:

| Removed (v3)                                            | v4 replacement                                                       |
|---------------------------------------------------------|----------------------------------------------------------------------|
| `setRoutePluginManager()` / `getRoutePluginManager()`   | Pass `RouteBuilderRegistry` to the stack constructor                 |
| `setDefaultParams()` / `setDefaultParam()`              | Pass `$defaultParams` to the stack constructor or builder options    |
| `setBaseUrl()` / `getBaseUrl()`                         | Pass `$pathOffset` to `TreeRouteStack::match($request, $pathOffset)` |
| `setRequestUri()` / `getRequestUri()`                   | Pass `'uri'` assemble option                                         |
| `addPrototype()` / `addPrototypes()` / `getPrototype()` | Removed                                                              |
| `init()` hook                                           | Removed                                                              |
| `RouteInterface::factory()`                             | Constructor or `RouteBuilderRegistry::build()`                       |

Built-in route classes (`Literal`, `Segment`, `Part`, etc.) are `final readonly`
and cannot be extended. Implement `RouteInterface` or `HttpRouteInterface`
directly if you need custom route behavior.

### Configuring a router at creation time

```php
// v3
$router = TreeRouteStack::factory(['routes' => $routes]);
$router->setDefaultParams(['lang' => 'en']);
$router->setRoutePluginManager($routePlugins);

// v4
$registry = $container->get(RouteBuilderRegistry::class);
$router   = new TreeRouteStack($registry, $routes, ['lang' => 'en']);

// Or via a builder:
$router = $registry->build(TreeRouteStack::class, [
    'routes'         => $routes,
    'default_params' => ['lang' => 'en'],
]);
```

Route stacks and composite routes (`Part`, `Chain`) take a
`RouteBuilderRegistry` in their constructor. The `route_plugins` option has
been removed.

## Route priority

The public `$priority` property on routes and stacks has been removed. Priority
is now set via constructor/builder options or route specifications and exposed
through `RouteInterface::getPriority()`.

```php
// v3
$route->priority = 10;
$router->addRoute('foo', $route);

// v4
$router->addRoute('foo', [
    'type'     => 'literal',
    'priority' => 10,
    'options'  => ['route' => '/foo'],
]);
```

Custom route implementations must implement `getPriority(): ?int`.

## `RouteStackInterface` — void returns

Stack mutator methods no longer return `$this` for fluent chaining:

```php
// v3
$router->addRoute('foo', $route)->addRoute('bar', $route);

// v4
$router->addRoute('foo', $route);
$router->addRoute('bar', $route);
```

Method signatures are now strictly typed:

```php
public function addRoute(string $name, array|RouteInterface $route, ?int $priority = null): void;
public function addRoutes(array $routes): void;
public function removeRoute(string $name): void;
public function setRoutes(array $routes): void;
```

## `RouteMatch` parameter typing

`setParam()` now requires a string value:

```php
// v3
$match->setParam('id', 42);

// v4
$match->setParam('id', '42');
```

`getParam()` returns `int|string|null`. Route parameters captured from URI
segments may still be returned as integers when parsed from the path.

## Configuration and service container changes

### `ConfigProvider` output

| v3                              | v4                                                                                 |
|---------------------------------|------------------------------------------------------------------------------------|
| `'dependencies'`                | `'dependencies'` (unchanged key)                                                   |
| `'route_manager'` (empty array) | **removed**                                                                        |
| (no router block)               | `'router' => ['router_class' => TreeRouteStack::class, 'route_builders' => [...]]` |

**Migration:** Register custom route builders in `dependencies.factories` and
map route types to builder service ids in `router.route_builders`:

```php
// v3 application config
'route_manager' => [
    'factories' => [
        MyCustomRoute::class => MyCustomRouteFactory::class,
    ],
],

// v4 application config
'dependencies' => [
    'factories' => [
        VanityRouteBuilder::class => VanityRouteBuilderFactory::class,
    ],
],
'router' => [
    'route_builders' => [
        VanityRoute::class => VanityRouteBuilder::class,
    ],
],
```

The `router.route_builders` key maps a route type or alias to a builder service
id. It is **not** the Service Manager `factories` shape — builders are
registered separately in `dependencies.factories`.

### Removed service aliases

The following aliases are no longer registered by the component:

| Removed alias                       | Target (v3)                  |
|-------------------------------------|------------------------------|
| `'HttpRouter'`                      | `TreeRouteStack::class`      |
| `'router'` / `'Router'`             | `RouteStackInterface::class` |
| `'RoutePluginManager'`              | `RoutePluginManager::class`  |
| `'Zend\Router\Http\TreeRouteStack'` | `TreeRouteStack::class`      |
| `'Zend\Router\RoutePluginManager'`  | `RoutePluginManager::class`  |
| `'Zend\Router\RouteStackInterface'` | `RouteStackInterface::class` |

**Migration:** Resolve services by FQCN:

```php
$router   = $container->get(RouteStackInterface::class);
$router   = $container->get(TreeRouteStack::class);
$registry = $container->get(RouteBuilderRegistry::class);
```

Re-register aliases in your application configuration if legacy string service
names are still referenced elsewhere in your codebase.

`RouterFactory` now delegates to `TreeRouteStack::class` instead of the
`'HttpRouter'` alias.

## Translator changes

`TranslatorAwareTreeRouteStack` no longer implements
`Laminas\I18n\Translator\TranslatorAwareInterface`. It now uses
`Laminas\Translator\TranslatorInterface` from the `laminas/laminas-translator`
package.

The following runtime setters have been removed:

- `setTranslator()`
- `setTranslatorEnabled()`
- `setTranslatorTextDomain()`
- `hasTranslator()`

**Migration:** Configure the translator at router creation time via
`TranslatorAwareTreeRouteStackBuilder` or the constructor. When using the
container-backed builder, a `TranslatorInterface` service is resolved
automatically if not passed in options:

```php
$registry = $container->get(RouteBuilderRegistry::class);
$router   = $registry->build(TranslatorAwareTreeRouteStack::class, [
    'routes'                 => $routes,
    'translator_text_domain' => 'default',
]);
```

`isTranslatorEnabled()` now returns `true` when a translator instance is
configured, and `false` when `translator` is `null`.

## Route builders

Routes are created via constructors or `RouteBuilderInterface` implementations,
not `factory()` methods or a plugin manager.

- `RouteBuilderInterface::build(array $options = []): RouteInterface` — builds
  a route from data-only options; object dependencies are constructor-injected
  into the builder.
- `RouteBuilderRegistry` — resolves builders by type or alias and delegates to
  `build()`. Obtain it via `$container->get(RouteBuilderRegistry::class)` with
  Mezzio / MVC (`ConfigProvider`).
- `router.route_builders` configuration maps route type/alias → builder service
  id (see [ConfigProvider output](#configprovider-output)).

**Leaf route example:**

```php
// v3
$route = Literal::factory([
    'route'    => '/foo',
    'defaults' => ['action' => 'index'],
]);

// v4 — constructor
$route = new Literal('foo', '/foo', ['action' => 'index']);

// v4 — registry
$route = $registry->build('literal', [
    'name'     => 'foo',
    'route'    => '/foo',
    'defaults' => ['action' => 'index'],
]);
```

Default route type registration lives in `RouteBuilderRegistry::defaultBuilderMap()`.
The `Wildcard` route type is no longer registered. v2 normalized route type
names (e.g. `laminasmvcrouterhttpsegment`) are no longer supported.

**Migration:** Use standard aliases (`segment`, `literal`, `part`, etc.) or
FQCNs when defining route types in configuration.

## Duplicate route names

Adding a route with a name that already exists now throws
`Laminas\Router\Exception\InvalidArgumentException`. Previously, the new route
silently replaced the existing one.

## Quick-reference checklist

| Area                                 | Action                                                                            |
|--------------------------------------|-----------------------------------------------------------------------------------|
| PHP version                          | Ensure 8.2+                                                                       |
| Service Manager                      | Optional; upgrade to ^4.5 only if using ConfigProvider / MVC                      |
| `RoutePluginManager`                 | Use `RouteBuilderRegistry` and route builders                                     |
| `RouteInterface::factory()`          | Use constructors or `$registry->build()`                                          |
| `Wildcard` routes                    | Migrate to `Segment`                                                              |
| `Http\RouteMatch`                    | Use `HttpRouteMatch`                                                              |
| `Http\RouteInterface`                | Use `HttpRouteInterface`                                                          |
| `Module`                             | Use `ConfigProvider`                                                              |
| `'route_manager'` config             | Register builders in `dependencies.factories`; map in `router.route_builders`     |
| Service aliases (`HttpRouter`, etc.) | Use FQCNs or re-add aliases in app config                                         |
| `assemble()` return value            | Call `->toString()` or use `AssembledUrl` properties                              |
| Requests                             | PSR-7 `RequestInterface`                                                          |
| Mutable router setup                 | Configure via constructor / builders                                              |
| Translator                           | `laminas-translator`; configure via builder or constructor                        |
| Canonical URLs                       | Pass `'uri' => $request->getUri()` with `'force_canonical'`                       |
| `laminas-uri`                        | Remove direct usage; use PSR-7 URIs + `AssembledUrl`                              |
| Custom route classes                 | Implement `getPriority()`, return `AssembledUrl`, accept PSR-7; provide a builder |
| Extending built-in routes            | Not possible (`final readonly`); implement interfaces instead                     |
| Fluent stack chaining                | Use separate method calls                                                         |
| Duplicate route names                | Handle `InvalidArgumentException` or use unique names                             |
