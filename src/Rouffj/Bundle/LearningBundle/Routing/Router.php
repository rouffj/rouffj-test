<?php

namespace Rouffj\Bundle\LearningBundle\Routing;

use Symfony\Bundle\FrameworkBundle\Routing\Router as BaseRouter;
use Symfony\Component\Routing\RouteCollection;

class Router extends BaseRouter
{
    public function setCollection(RouteCollection $collection)
    {
        $this->collection = $collection;
    }
}
