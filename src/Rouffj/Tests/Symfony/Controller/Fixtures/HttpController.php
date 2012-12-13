<?php

namespace Rouffj\Tests\Symfony\Controller\Fixtures;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HttpController extends Controller
{
    /**
     * @Route("/query")
     */
    public function queryAction()
    {
        $request = $this->get('request');
        $response = array(
            'param1' => $request->query->get('param1'), // how to get the value of a specific query param?
            'param1_defined' => $request->query->has('param1'), // How to check if a query param is defined?
            'param2' => $request->query->get('param2', 'defaultValueParam2'), // How to assign a default value on a query param?
            'param3' => $request->query->get('param3'), // What is the value returned for a non existing query param?
        );
        return new Response(json_encode($response));
    }

    /**
     * @Route("/server")
     */
    public function serverAction()
    {
        $request = $this->get('request');
        $response = array(
            'server_name' => $request->server->get('SERVER_NAME'),
            'server_protocol' => $request->server->get('SERVER_PROTOCOL'),
        );
        return new Response(json_encode($response));
    }

    /**
     * @Route("/create_session")
     */
    public function createSessionAction()
    {
        $request = $this->get('request');
        $request->getSession()->set('sessionA', 'test'); // How to set a new entry to the session?

        return new Response('');
    }

    /**
     * @Route("/read_session")
     */
    public function readSessionAction()
    {
        $request = $this->get('request');

        $response = array(
            'sessionA_defined' => $request->getSession()->has('sessionA')
        );

        return new Response(json_encode($response));
    }

    /**
     * @Route("/flash")
     */
    public function flashAction()
    {
        $request = $this->get('request');

        if ($request->isMethod('POST')) {
            $request->getSession()->getFlashBag()->add('notice', 'The article has been saved');
            $request->getSession()->getFlashBag()->add('notice', 'The comment has been saved');
            $request->getSession()->getFlashBag()->add('error', 'msg');
            return new Response('');
        }

        $response = array(
            'notice_flashes' => $request->getSession()->getFlashBag()->get('notice'), // How to get all flashes belonging to one category (eg. notice) and delete it.
            'error_flashes'  => $request->getSession()->getFlashBag()->peek('error'), // How to get flashes belonging to one category without delete it.
        );

        return new Response(json_encode($response));
    }

    /**
     * @Route("/404")
     */
    public function error404Action()
    {
        throw $this->createNotFoundException('The product does not exist');
    }

    /**
     * @Route("/temporary-redirection")
     */
    public function temporaryRedirectAction()
    {
        return $this->redirect('http://www.google.fr');
    }

    /**
     * @Route("/permanent-redirection")
     */
    public function permanentRedirectAction()
    {
        return $this->redirect('http://www.google.fr', 301);
    }

    /**
     * @Route("/forward")
     */
    public function forwardAction()
    {
        return $this->forward('Rouffj\Tests\Symfony\Controller\Fixtures\HttpController::forwardedAction',
            array('param1' => 'value1', 'param2' => 'value2'), // How to transmit placeholders?
            array('query1' => 'queryValue1') // How to transmit query params?
        );
    }

    public function forwardedAction($param1, $param2)
    {
        return new Response(sprintf('forwardedAction.%s.%s.%s', $param1, $param2, $this->get('request')->query->get('query1')));
    }

    /**
     * @Route("/shortcuts")
     * @link http://api.symfony.com/master/Symfony/Bundle/FrameworkBundle/Controller/Controller.html
     */
    public function shortcutsAction()
    {
        $request = $this->get('request');

        $response = array(
            'request' => get_class($this->getRequest()),
            'doctrine' => get_class($this->getDoctrine()),
            'not_found' => get_class($this->createNotFoundException()),
        );

        return new Response(json_encode($response));
    }
}
