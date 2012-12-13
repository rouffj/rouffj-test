<?php

namespace Rouffj\Tests\Symfony\Templating\Fixtures;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/twig")
 */
class TwigController extends Controller
{
    /**
     * @Route("/app")
     */
    public function appAction()
    {
        return $this->render('twig/app.html.twig');
    }
}
