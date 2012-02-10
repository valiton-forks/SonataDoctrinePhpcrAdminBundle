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
namespace Sonata\DoctrinePHPCRAdminBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

use Doctrine\Common\Persistence\ManagerRegistry;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Admin\AdminInterface;

class AdminDispatcherListener implements EventSubscriberInterface
{
    protected $manager;

    protected $pool;

    protected $adminUrl;

    protected $logger;

    /**
     * @param \Doctrine\Common\Persistence\ManagerRegistry $manager
     * @param \Sonata\AdminBundle\Admin\Pool $pool
     * @param string $adminUrl
     */
    public function __construct(ManagerRegistry $manager, Pool $pool, $adminUrl = '@/admin/tree(.*)@', LoggerInterface $logger = null)
    {
        $this->manager  = $manager;
        $this->pool     = $pool;
        $this->adminUrl = $adminUrl;
        $this->logger   = $logger;
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @return void
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        if (!preg_match($this->adminUrl, $request->getPathInfo())) {
            return;
        }

        $node = $this->manager->getConnection()->getNode($request->get('id', '/'));

        if (!$node) {
            throw new NotFoundHttpException(sprintf('There is no node linked to the path %s', $request->get('id', '/')));
        }

        if ($adminId = $request->get('admin')) {
            $admin = $this->pool->getAdminByAdminCode($adminId);
        } else if ($node->hasProperty('phpcr:class')) {
            $class = $node->getProperty('phpcr:class')->getValue();

            // retrieve the related Admin Instance
            if (!$this->pool->hasAdminByClass($class)) {
                return;
            }

            $admin = $this->pool->getAdminByClass($class);
        } else {
            return;
        }

        $action = $request->get('action', 'edit');

        if ($action == 'list') {
            return;
        }

        $route = $admin->getRoute($action);

        if (!$route) {
            throw new NotFoundHttpException(sprintf('The action `%s` does not exist', $action));
        }

        if ($this->logger) {
            $this->logger->info(sprintf("Alter the request object, _controller: %s, _sonata_admin: %s", $route->getDefault('_controller'), $admin->getCode()));
        }

        // Alter the request
        $request->attributes->set('_controller', $route->getDefault('_controller'));
        $request->attributes->set('_sonata_admin', $admin->getCode());
    }

    static public function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 31)),
        );
    }
}