<?php

namespace Rouffj\Tests\Twig;

use Rouffj\Tests\TestCase;

class TwigTest extends TestCase
{
    private  $twig = null;

    private $templates = array(
        'tpl1.html.twig' => '{% block nb1 %}block body 1{% endblock%}{% block nb2 %}block body 2{% endblock %}',
        'tpl2.html.twig' => '{% extends "tpl1.html.twig" %} {% block nb1 %}{{ parent() }} block child body 1{% endblock%}',
    );

    public function doSetUp()
    {
        $this->twig = $this->container->get('twig');
        $this->twig->setLoader(new \Twig_Loader_String());
    }

    public function testHowToApplyFilterOnOneLineString()
    {
        $this->assertEquals('STRING', $this->twig->render('{{ "string"|upper }}'));
    }

    public function testHowToApplyFilterOnMultipleLineString()
    {
        $this->assertEquals("MY BIG STRING\nON MULTIPLE LINES.", $this->twig->render(
'{% filter upper%}my big string
on multiple lines.{% endfilter %}'
        ));
    }

    public function testHowToChainFilters()
    {
        // inlined chain
        $this->assertEquals('String', $this->twig->render('{{ "string"|upper|capitalize }}'));
        // chain in block style filter
        $this->assertEquals('My big string', $this->twig->render('{% filter upper|capitalize %}mY bIg sTring{% endfilter %}'));
    }

    public function testHowToDisableAutoEscaping()
    {
        $this->assertEquals('&lt;p&gt;my HTML&lt;/p&gt;', $this->twig->render('{% set var = "<p>my HTML</p>"%}{{ var }}'),
            'By default autoescaping is enabled'
        );

        // Autoescaping can be disabled locally
        $this->assertEquals('<p>my HTML</p>', $this->twig->render('{% set var = "<p>my HTML</p>"%}{% autoescape false %}{{ var }}{% endautoescape %}'));
        $this->assertEquals('<p>my HTML</p>', $this->twig->render('{% set var = "<p>my HTML</p>"%}{{ var|raw }}'));

        // Autoescaping can be disabled globally
        $this->markTestIncomplete('removing escaper extension does not disable autoescaping');
        $this->twig->removeExtension('escaper');
        $this->assertEquals('<p>my HTML</p>', $this->twig->render('{% set var = "<p>my HTML</p>"%}{{ var }}'));
    }

    public function testHowToConcatenateThings()
    {
        // use concatenate operator ~
        $this->assertEquals('str1str2', $this->twig->render('{{ "str1"~"str2" }}'));
        $this->assertEquals('str1str2', $this->twig->render('{{ "str1"~var }}', array('var' => 'str2')));

        // use #{}
        $this->assertEquals('str1str2', $this->twig->render('{{ "str1str#{var+1}"}}', array('var' => 1)));
    }

    public function testHowToMixPhpWithTwigTemplate()
    {
        // When PHP snippet is written in Twig template, the php is not interpreted
        $this->assertEquals('<?php echo 1 ?> twig', $this->twig->render('<?php echo 1 ?> {{ "twig" }}'));
    }

    public function testHowToRenderSpecificBlockFromTemplate()
    {
        $this->twig->setLoader(new \Twig_Loader_Array($this->templates));

        $tpl = $this->container->get('twig')->loadTemplate('tpl1.html.twig');
        $this->assertEquals('block body 1', $tpl->renderBlock('nb1', array()));
        $this->assertEquals('block body 2', $tpl->renderBlock('nb2', array()));

        $tpl = $this->container->get('twig')->loadTemplate('tpl2.html.twig');
        // by default renderBlock renders parent block if exists
        $this->assertEquals('block body 1 block child body 1', $tpl->renderBlock('nb1', array()));
        // renderParentBlock allow to render only the parent of the current block
        $this->assertEquals('block body 1', $tpl->renderParentBlock('nb1', array()));
    }

    public function testHowToKnowBlocksAvailableFromTemplate()
    {
        $this->twig->setLoader(new \Twig_Loader_Array($this->templates));

        $tpl = $this->container->get('twig')->loadTemplate('tpl1.html.twig');

        // enumerates all block names from template
        $this->assertEquals(array('nb1', 'nb2'), $tpl->getBlockNames());

        // check if a block with given name exists
        $this->assertEquals(true, $tpl->hasBlock('nb1'));
        $this->assertEquals(false, $tpl->hasBlock('nb6'));
    }
}
