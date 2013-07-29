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

/*
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author Nacho Martín <nitram.ohcan@gmail.com>
 */
class AttachAdminCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->getDefinition('sonata.admin.phpcr.dispatcher')) {
            return;
        }

        $dispatcher = $container->getDefinition('sonata.admin.phpcr.dispatcher');

        foreach ($container->findTaggedServiceIds('sonata.admin.phpcr') as $id => $tags) {
            $admin = $container->getDefinition($id);
            $class = $admin->getArgument(1);

            $dispatcher->addMethodCall('addAdmin', array($class, $id));
        }
    }
}