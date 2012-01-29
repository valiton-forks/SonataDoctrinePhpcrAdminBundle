<?php

namespace Sonata\DoctrinePHPCRAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class TreeController extends Controller
{

    /**
     * This must be done in a the listener ...
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dispatcherAction($id)
    {
        $path = $id ?: '/';

        $node = $this->getPHPCRSession()->getNode($path);

        $action = $this->getRequest()->get('action', 'list');

        if ($action == 'list') {
            return $this->executeList($node);
        }

        if ($adminId = $this->getRequest()->get('admin')) {
            $admin = $this->getAdminPool()->getAdminByAdminCode($adminId);
        } else {
            $class = $node->getProperty('phpcr:class')->getValue();

            // retrieve the related Admin Instance
            if (!$this->getAdminPool()->hasAdminByClass($class)) {
                throw new NotFoundHttpException(sprintf('There is no admin linked to the class %s', $class));
            }

            $admin = $this->getAdminPool()->getAdminByClass($class);
        }

        $route = $admin->getRoute($action);

        // Alter the request
        $request = $this->container->get('request');
        $request->attributes->set('_controller', $route->getDefault('_controller'));
        $request->attributes->set('_sonata_admin', $admin->getCode());

        // execute the controller
        $controller = $this->container->get('controller_resolver')->getController($request);
        $arguments = $this->container->get('controller_resolver')->getArguments($request, $controller);

        // call controller
        $response = call_user_func_array($controller, $arguments);

        return $response;
    }

    public function executeList($node)
    {
        return $this->render('SonataDoctrinePHPCRAdminBundle:Tree:index.html.twig', array(
            'node'   => $node,
        ));
    }

    /**
     * @param $view
     * @param array $parameters
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        $parents = array();

        if (isset($parameters['node'])) {
            $parent = $parameters['node'];
            while($parent->getDepth() > 0) {
                $parents[] = $parent;
                $parent = $parent->getParent();
            }

            $parents = array_reverse($parents);
        }

        $parameters['base_template'] = $this->getAdminPool()->getTemplate('layout');
        $parameters['admin_pool']    = $this->getAdminPool();
        $parameters['parents']       = $parents;

        return parent::render($view, $parameters, $response);
    }

    /**
     * @return \Jackalope\Session
     */
    public function getPHPCRSession()
    {
        return $this->container->get('doctrine_phpcr.default_session');
    }

    /**
     * @return \Sonata\AdminBundle\Admin\Pool
     */
    public function getAdminPool()
    {
        return $this->container->get('sonata.admin.pool');
    }
}
