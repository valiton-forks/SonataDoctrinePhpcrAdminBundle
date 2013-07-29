<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) 2010-2011 Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\DoctrinePHPCRAdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin as BaseAdmin;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * Label of admin
 *
 *
 *
 */

/**
 * Extend the Admin class to incorporate phpcr changes.
 *
 * Especially make sure that there are no duplicated slashes in the generated urls
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AdminDispatcher extends BaseAdmin
{
    protected $container;

    protected $admins = array();

    /**
     * Attach a new Admin Id to the main admin dispatcher, pass an id to
     * avoid loading all information into memory
     *
     * @param string $class
     * @param string $id
     */
    public function addAdmin($class, $id)
    {
        $this->admins[$class] = $id;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * @param mixed $class
     */
    public function findAdmin($class)
    {
        /// find a better way of dealing with inheritance and proxy class
        if (is_object($class)) {
            $class = get_class($class);
        }

        foreach ($this->admins as $klass => $id) {
            if ($class == $klass) {
                return $this->container->get($id);
            }
        }

        throw new \RuntimeException(sprintf('Unable to find a valid admin for the class %s', $class));
    }

    /**
     * @param object $subject
     * @param string $template
     */
    public function getTemplateForObject($subject, $template)
    {

    }

    /**
     * @return \Sonata\AdminBundle\Route\RouteCollection|RouteCollection
     */
    public function getRoutes()
    {
        $collection = new RouteCollection(null, null, null, null);

        foreach($this->admins as $class => $id) {
            $admin = $this->container->get($id);

            $collection->addCollection($admin->getRoutes());
        }

        return $collection;
    }
}