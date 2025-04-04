<?php

declare(strict_types=1);

namespace Laminas\ServiceManager\AbstractFactory;

use ArrayObject;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function array_map;
use function array_values;
use function is_array;
use function json_encode;

use const JSON_THROW_ON_ERROR;

final class ConfigAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Factory can create the service if there is a key for it in the config
     *
     * {@inheritdoc}
     */
    public function canCreate(ContainerInterface $container, string $requestedName): bool
    {
        if (! $container->has('config')) {
            return false;
        }
        $config = $container->get('config');
        if (! isset($config[self::class])) {
            return false;
        }
        $dependencies = $config[self::class];

        return is_array($dependencies) && array_key_exists($requestedName, $dependencies);
    }

    /** {@inheritDoc} */
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): mixed
    {
        if (! $container->has('config')) {
            throw new ServiceNotCreatedException('Cannot find a config array in the container');
        }

        $config = $container->get('config');

        if (! (is_array($config) || $config instanceof ArrayObject)) {
            throw new ServiceNotCreatedException('Config must be an array or an instance of ArrayObject');
        }
        if (! isset($config[self::class])) {
            throw new ServiceNotCreatedException('Cannot find a `' . self::class . '` key in the config array');
        }

        $dependencies = $config[self::class];

        if (
            ! is_array($dependencies)
            || ! array_key_exists($requestedName, $dependencies)
            || ! is_array($dependencies[$requestedName])
        ) {
            throw new ServiceNotCreatedException('Service dependencies config must exist and be an array');
        }

        $serviceDependencies = $dependencies[$requestedName];

        if ($serviceDependencies !== array_values(array_map('strval', $serviceDependencies))) {
            $problem = json_encode(array_map('gettype', $serviceDependencies), JSON_THROW_ON_ERROR);
            throw new ServiceNotCreatedException(
                'Service dependencies config must be an array of strings, ' . $problem . ' given'
            );
        }

        $arguments = array_map($container->get(...), $serviceDependencies);

        return new $requestedName(...$arguments);
    }
}
