<?php

namespace Rouffj\Tests\Twig;

use Rouffj\Tests\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TwigTest extends WebTestCase
{
    private  $container = null;
    private  $twig = null;

    public function setUp()
    {
        $this->container = $this->createClient()->getContainer();
        $this->twig = $this->container->get('twig');
        $this->twig->setLoader(new \Twig_Loader_String());
    }

    public function testHowtoSetFilterInlined()
    {
        $this->assertEquals('STRING', $this->twig->render('{{ "string"|upper }}'));
    }

    public function testHowtoApplyFilterOnBigString()
    {
        $this->assertEquals("MY BIG STRING\nON MULTIPLE LINES.", $this->twig->render(
'{% filter upper%}my big string
on multiple lines.{% endfilter %}'
        ));
    }

    public function testHowtoChainFilters()
    {
        // inlined chain
        $this->assertEquals('String', $this->twig->render('{{ "string"|upper|capitalize }}'));
        // chain in block style filter
        $this->assertEquals('My big string', $this->twig->render('{% filter upper|capitalize %}mY bIg sTring{% endfilter %}'));
    }

    public function testHowtoDisableAutoEscaping()
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

    public function testHowtoConcatenateThings()
    {
        // use concatenate operator ~
        $this->assertEquals('str1str2', $this->twig->render('{{ "str1"~"str2" }}'));
        $this->assertEquals('str1str2', $this->twig->render('{{ "str1"~var }}', array('var' => 'str2')));

        // use #{}
        $this->assertEquals('str1str2', $this->twig->render('{{ "str1str#{var+1}"}}', array('var' => 1)));
    }

    public function testHowtoMixPHPWithTwigTemplate()
    {
        // When PHP snippet is written in Twig template, the php is not interpreted
        $this->assertEquals('<?php echo 1 ?> twig', $this->twig->render('<?php echo 1 ?> {{ "twig" }}'));
    }
}
