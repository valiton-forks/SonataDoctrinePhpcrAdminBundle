<?php

namespace Sonata\DoctrinePHPCRAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class TreeController extends Controller
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($id)
    {
        $node = $this->getPHPCRSession()->getNode($id ?: '/');

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
