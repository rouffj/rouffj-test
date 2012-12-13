<?php

namespace Rouffj\Bundle\LearningBundle\Twig\Extension;

class LearningExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'class' => new \Twig_Filter_Method($this, 'getClass')
        );
    }

    public function getClass($class)
    {
        return strtr(get_class($class), array('\\' => '\\\\'));
    }

    public function getName()
    {
        return 'rouffj_learning.learning';
    }
}
