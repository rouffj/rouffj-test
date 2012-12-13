<?php

namespace Rouffj\Tests\Symfony\Controller\Fixtures;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RoutingController extends Controller
{
    /**
     * @Route("/routeInfo", defaults={"_format"="html", "_locale"="fr"}, requirements={"_format"="html|txt", "_locale"="fr|en"})
     */
    public function routeInfoAction($_route, $_controller, $_format, $_locale)
    {
        $request = $this->get('request');
        $response = array(
            '_route' => $_route,
            '_controller' => $_controller,
            '_format' => $_format, // to get _format you have to specify _format in defaults and requirements sections.
            '_locale' => $_locale, // to get _locale you have to specify it in defaults and requirements sections.
        );

        return new Response(json_encode($response));
    }
}
