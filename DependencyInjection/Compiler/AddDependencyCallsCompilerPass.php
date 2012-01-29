<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Add all dependencies to the Admin class, this avoid to write too many lines
 * in the configuration files.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AddDependencyCallsCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $tags) {
            foreach ($tags as $attributes) {
                if ('doctrine_phpcr' !== $attributes['manager_type']) {
                    continue;
                }

                $this->applyDefaults($container, $id, $attributes);
            }
        }
    }

    /**
     * Apply the default values required by the AdminInterface to the Admin service definition
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $serviceId
     * @param array $attributes
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    public function applyDefaults(ContainerBuilder $container, $serviceId, array $attributes = array())
    {
        $definition = $container->getDefinition($serviceId);
        $definition->addMethodCall('setRouteGenerator', array(new Reference('sonata.admin.route.path_generator')));

        return $definition;
    }
}
