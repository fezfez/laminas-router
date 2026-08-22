<?php

declare(strict_types=1);

namespace Laminas\Router;

use Laminas\Router\Http\Chain;
use Laminas\Router\Http\ChainFactory;
use Laminas\Router\Http\Hostname;
use Laminas\Router\Http\HostnameFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\LiteralFactory;
use Laminas\Router\Http\Method;
use Laminas\Router\Http\MethodFactory;
use Laminas\Router\Http\Part;
use Laminas\Router\Http\PartFactory;
use Laminas\Router\Http\Placeholder;
use Laminas\Router\Http\PlaceholderFactory;
use Laminas\Router\Http\Regex;
use Laminas\Router\Http\RegexFactory;
use Laminas\Router\Http\Scheme;
use Laminas\Router\Http\SchemeFactory;
use Laminas\Router\Http\Segment;
use Laminas\Router\Http\SegmentFactory;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

use function array_replace_recursive;
use function get_debug_type;
use function sprintf;

/**
 * Plugin manager implementation for routes
 *
 * Enforces that routes retrieved are instances of RouteInterface.
 *
 * The manager is marked to not share by default, in order to allow multiple
 * route instances of the same type.
 *
 * @see ServiceManager for expected configuration shape
 *
 * @psalm-type InstanceType = RouteInterface
 * @extends AbstractPluginManager<InstanceType>
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class RoutePluginManager extends AbstractPluginManager
{
    /**
     * @psalm-var ServiceManagerConfiguration
     */
    private const CONFIG = [
        'aliases'   => [
            'chain'       => Chain::class,
            'Chain'       => Chain::class,
            'hostname'    => Hostname::class,
            'Hostname'    => Hostname::class,
            'hostName'    => Hostname::class,
            'HostName'    => Hostname::class,
            'literal'     => Literal::class,
            'Literal'     => Literal::class,
            'method'      => Method::class,
            'Method'      => Method::class,
            'part'        => Part::class,
            'Part'        => Part::class,
            'regex'       => Regex::class,
            'Regex'       => Regex::class,
            'scheme'      => Scheme::class,
            'Scheme'      => Scheme::class,
            'segment'     => Segment::class,
            'Segment'     => Segment::class,
            'Placeholder' => Placeholder::class,
            'placeholder' => Placeholder::class,
        ],
        'factories' => [
            Chain::class       => ChainFactory::class,
            Hostname::class    => HostnameFactory::class,
            Literal::class     => LiteralFactory::class,
            Method::class      => MethodFactory::class,
            Part::class        => PartFactory::class,
            Regex::class       => RegexFactory::class,
            Scheme::class      => SchemeFactory::class,
            Segment::class     => SegmentFactory::class,
            Placeholder::class => PlaceholderFactory::class,
        ],
    ];
    /**
     * Only RouteInterface instances are valid
     *
     * @var class-string
     */
    protected string $instanceOf = RouteInterface::class;

    /**
     * Do not share instances.
     */
    protected bool $sharedByDefault = false;

    /** @param ServiceManagerConfiguration $config */
    public function __construct(ContainerInterface $container, array $config = [])
    {
        /** @psalm-var ServiceManagerConfiguration $config Psalm cannot infer this after merge */
        $config = array_replace_recursive(self::CONFIG, $config);
        parent::__construct($container, $config);
        if ($container instanceof ServiceManager) {
            $container->setService(self::class, $this);
        }
    }

    /**
     * Validate a route plugin. (v2)
     *
     * @throws InvalidServiceException
     * @psalm-assert InstanceType $instance
     */
    public function validate(mixed $instance): void
    {
        if (! $instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                'Plugin of type %s is invalid; must implement %s',
                get_debug_type($instance),
                RouteInterface::class
            ));
        }
    }

}
