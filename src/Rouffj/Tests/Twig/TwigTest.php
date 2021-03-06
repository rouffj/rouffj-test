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
        $this->twig->setCache(false);
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
        //   1) By set escaper strategy to false
        $this->twig->getExtension('escaper')->setDefaultStrategy(false);
        $this->assertEquals('<p>1</p>', $this->twig->render('{% set var = "<p>1</p>"%}{{ var }}'));

        //   2) By removing the extension
        $this->twig->getExtension('escaper')->setDefaultStrategy('html');
        $this->twig->removeExtension('escaper');
        $this->assertEquals('<p>2</p>', $this->twig->render('{% set var = "<p>2</p>"%}{{ var }}'));
    }

    public function testHowToConcatenateThings()
    {
        // use concatenate operator ~
        $this->assertEquals('str1str2', $this->twig->render('{{ "str1"~"str2" }}'));
        $this->assertEquals('str1str2', $this->twig->render('{{ "str1"~var }}', array('var' => 'str2')));

        // use #{}
        $this->assertEquals('str1str2', $this->twig->render('{{ "str1str#{var+1}"}}', array('var' => 1)));
    }

    public function testHowToSumTwoNumbersInString()
    {
        $this->assertEquals('Symfony 2.1', $this->twig->render('{{ "Symfony #{2.0 + 0.1}"  }}'));
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

    /**
     * @depends testHowToAccessAppVariableFromTemplate
     */
    public function testHowToAccessSymfonyEnvironmentFromTemplate($app)
    {
        $this->assertEquals('test', $app->getEnvironment(), 'should be app.environment in twig');
    }

    /**
     * The twig "replace" filter use behind the scene the strtr PHP function
     */
    public function testHowToUseReplaceFilter()
    {
        $string = 'I like %this% and %that%.';
        $this->assertEquals(
            strtr($string, array('%this%' => 'foo', '%that%' => 'bar')),
            $this->twig->render('{{ string|replace({"%this%": "foo", "%that%": "bar"})  }}', array('string' => $string)),
            'use a "replace" filter is equivalent to "strtr" PHP function'
        );
    }

    public function testHowToUseLoopVariable()
    {
        // How to access parent loop
        $twig =<<<TEST
{% for item in 0..1 %}
{% for item in 0..2 %}{{ loop.parent.loop.index0 }}.{{ loop.index0 }}|{% endfor %}
{% endfor %}
TEST;
        $this->assertEquals('0.0|0.1|0.2|1.0|1.1|1.2|', $this->twig->render($twig), 'to access parent loop info we have to check loop.parent.loop, not directly loop.parent');

        // How to check if the current iteration if the first or the last ?
        $twig =<<<TEST
{% for item in 0..2 %}
{% if loop.first%}{{loop.index0}}.{% endif %}{% if loop.last%}{{loop.index0}}{% endif %}
{% endfor%}
TEST;
        $this->assertEquals('0.2', $this->twig->render($twig));
    }
}
