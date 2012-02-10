<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Listener;

use Sonata\DoctrinePHPCRAdminBundle\Listener\AdminDispatcherListener;
use PHPCR\SessionInterface;
use PHPCR\PropertyInterface;

use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class AdminDispatcherListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testExceptionNoRouteDefined()
    {
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getRoute')->will($this->returnValue(false));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())->method('get')->will($this->returnValue($admin));

        $property = $this->getMock('Sonata\DoctrinePHPCRAdminBundle\Tests\PHPCR\PropertyInterface');
        $property->expects($this->once())->method('getValue')->will($this->returnValue('Foo\Bar'));

        $node = $this->getMock('Sonata\DoctrinePHPCRAdminBundle\Tests\PHPCR\NodeInterface');
        $node->expects($this->once())->method('getProperty')->will($this->returnValue($property));
        $node->expects($this->once())->method('hasProperty')->will($this->returnValue(true));

        $session = $this->getMock('PHPCR\SessionInterface');
        $session->expects($this->once())->method('getNode')->will($this->returnValue($node));

        $manager = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $manager->expects($this->once())->method('getConnection')->will($this->returnValue($session));

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminClasses(array(
            'Foo\Bar' => 'service.id'
        ));

        $dispatcher = new AdminDispatcherListener($manager, $pool);

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $event = new GetResponseEvent($kernel, $this->getRequest(), HttpKernelInterface::MASTER_REQUEST);
        $dispatcher->onKernelRequest($event);
    }

    public function testValidDispatch()
    {
        $route = new \Symfony\Component\Routing\Route('/foo/bar', array(
            '_controller' => 'SonataAdminBundle:CRUD:list'
        ));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getRoute')->will($this->returnValue($route));
        $admin->expects($this->once())->method('getCode')->will($this->returnValue('service.id'));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())->method('get')->will($this->returnValue($admin));

        $property = $this->getMock('Sonata\DoctrinePHPCRAdminBundle\Tests\PHPCR\PropertyInterface');
        $property->expects($this->once())->method('getValue')->will($this->returnValue('Foo\Bar'));

        $node = $this->getMock('Sonata\DoctrinePHPCRAdminBundle\Tests\PHPCR\NodeInterface');
        $node->expects($this->once())->method('getProperty')->will($this->returnValue($property));
        $node->expects($this->once())->method('hasProperty')->will($this->returnValue(true));

        $session = $this->getMock('PHPCR\SessionInterface');
        $session->expects($this->once())->method('getNode')->will($this->returnValue($node));

        $manager = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $manager->expects($this->once())->method('getConnection')->will($this->returnValue($session));

        $pool = new Pool($container, 'title', 'logo');
        $pool->setAdminClasses(array(
            'Foo\Bar' => 'service.id'
        ));

        $dispatcher = new AdminDispatcherListener($manager, $pool);

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $event = new GetResponseEvent($kernel, $this->getRequest(), HttpKernelInterface::MASTER_REQUEST);
        $dispatcher->onKernelRequest($event);

        $this->assertEquals('SonataAdminBundle:CRUD:list', $event->getRequest()->attributes->get('_controller'));
        $this->assertEquals('service.id', $event->getRequest()->attributes->get('_sonata_admin'));
    }

    private function getRequest()
    {
        $query = array(
            'id' => '/path/to/node',
            'action' => 'edit'
        );

        $server = array(
            'REQUEST_URI' => '/admin/tree/path/to/node'
        );

        return new Request($query, array(), array(), array(), array(), $server);
    }
}