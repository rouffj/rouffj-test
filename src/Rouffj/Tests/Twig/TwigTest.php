<?php

namespace Rouffj\Tests\Twig;

use Rouffj\Tests\TestCase;

class TwigTest extends TestCase
{
    private  $twig = null;

    private $templates = array(
        'tpl1.html.twig' => '{% block nb1 %}block body 1{% endblock%}{% block nb2 %}block body 2{% endblock %}',
        'tpl2.html.twig' => '{% extends "tpl1.html.twig" %} {% block nb1 %}{{ parent() }} block child body 1{% endblock%}',
        'tpl3.html.twig' => '{% set var1 = "bar" %}{% if (1 == 1) %} {% set var2 = "test" %} {% endif %}{% block nb1 %}block body 1{% endblock%}{% macro macro1() %}test{% endmacro%}{% include "tpl4.html.twig" %}',
        'tpl4.html.twig' => '{% set var3 = "barss" %}',
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

    public function testHowToAccessAppVariableFromTemplate()
    {
        $this->assertEquals(array('assetic'), array_keys($this->container->get('twig')->getGlobals()), 'no app variable exists without one call to "templating" service');
        $this->container->get('templating');
        $globals = $this->container->get('twig')->getGlobals();
        $this->assertEquals(array('app', 'assetic'), array_keys($globals), 'after "templating" call the "app" is available');
        $this->assertEquals('Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables', get_class($globals['app']));
        $this->assertEquals('', $this->twig->render('{{ app.user }}'));

        return $globals['app'];
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

    public function testHowToTransformTwigTemplateIntoAST()
    {
        $stream = $this->twig->tokenize($this->templates['tpl3.html.twig'], 'tpl3.html.twig');
        // this single line allow to transform a stream of token into a Tree of nodes (AST).
        $nodes = $this->twig->parse($stream);

        //echo $nodes;
        $this->assertInstanceOf('Twig_Node_Module', $nodes);

        $this->assertEquals('tpl3.html.twig', $nodes->getAttribute('filename'));
        $this->assertInstanceOf('Twig_Node_Body', $nodes->getNode('body'));
        $this->assertEquals(null, $nodes->getNode('parent'), 'A Twig template could have a body OR a parent, but not either.');
        $this->assertInstanceOf('Twig_Node', $nodes->getNode('blocks'));
        $this->assertInstanceOf('Twig_Node', $nodes->getNode('macros'));
        //echo $nodes;
        // Allow to retrieve 
        $this->assertInstanceOf('Twig_Node_Body', $nodes->getNode('blocks')->getNode('nb1'));
        $this->assertInstanceOf('Twig_Node_Macro', $nodes->getNode('macros')->getNode('macro1'));
    }

    public function testHowToCountTheNumberOfNodeMadeInASingleTemplate()
    {
        $stream = $this->twig->tokenize($this->templates['tpl3.html.twig']);
        $nodes = $this->twig->parse($stream);
        $entryBody = $nodes->getNode('body');
        $nbSets = 0;
        $this->foundSetNodes($entryBody, $nbSets, '\Twig_Node_Set');

        $this->assertEquals(2, $nbSets);
    }

    private function foundSetNodes($node, &$nbSets, $nodeType)
    {
        if ($node === null) {
            return;
        }

        if ($node instanceof $nodeType) {
            $nbSets += 1;
        }

        // code which activate the recursivity
        if ($node->count() > 0) {
            foreach ($node as $n) {
                $this->foundSetNodes($n, $nbSets, $nodeType);
            }
        }
    }
}
