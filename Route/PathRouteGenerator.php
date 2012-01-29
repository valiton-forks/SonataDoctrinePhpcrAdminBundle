<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Sonata\DoctrinePHPCRAdminBundle\Route;

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Routing\RouterInterface;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PathRouteGenerator implements RouteGeneratorInterface
{
    private $router;

    private $container;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(RouterInterface $router, ContainerInterface $container)
    {
        $this->router    = $router;
        $this->container = $container;
    }

    /**
     * @param $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    public function generate($name, array $parameters = array(), $absolute = false)
    {
        return $this->router->generate($name, $parameters, $absolute);
    }

    /**
     * @throws \RuntimeException
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param $name
     * @param array $parameter
     * @param bool $absolute
     * @return string
     */
    public function generateUrl(AdminInterface $admin, $name, array $parameters = array(), $absolute = false)
    {
        // if the admin is linked to a parent FieldDescription (ie, embedded widget)
        if ($admin->hasParentFieldDescription()) {
            // merge link parameter if any provided by the parent field
            $parameters = array_merge($parameters, $admin->getParentFieldDescription()->getOption('link_parameters', array()));

            $parameters['uniqid']  = $admin->getUniqid();
            $parameters['code']    = $admin->getCode();
            $parameters['pcode']   = $admin->getParentFieldDescription()->getAdmin()->getCode();
            $parameters['puniqid'] = $admin->getParentFieldDescription()->getAdmin()->getUniqid();
        }

        if ($name == 'update' || substr($name, -7) == '|update') {
            $parameters['uniqid'] = $admin->getUniqid();
            $parameters['code']   = $admin->getCode();
        }

        // allows to define persistent parameters
        if ($admin->hasRequest()) {
            $parameters = array_merge($admin->getPersistentParameters(), $parameters);
        }

        $route = $admin->getRoute($name);

        if (!$route) {
            throw new \RuntimeException(sprintf('unable to find the route `%s`', $name));
        }

        $parameters['action'] = $name;

        if (isset($parameters['id'])) {
            $path = $parameters['id'];
            unset($parameters['id']);
        } else {
            $path = $this->container->get('request')->get('id');
        }

        $parameters['id'] = '__PATH__';
        $parameters['admin'] = $admin->getCode();

        $url = $this->router->generate('sonata_admin_tree', $parameters, $absolute);

        return str_replace('__PATH__', $path, $url);
    }
}