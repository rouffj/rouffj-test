<?php

namespace Rouffj\Tests\Symfony\Templating;

use Rouffj\Tests\TestCase;

class TemplatingTest extends TestCase
{
    public function doSetUp()
    {
        // Allow Fixtures/*Controller can load templates from relative path
        $this->twig = $this->container->get('twig');
        $this->twig->setLoader(new \Twig_Loader_Filesystem(__DIR__.'/Fixtures'));
    }

    /**
     * @TODO: create a class filter allows to make {{ app.security|class }} // Symfony\Component\Security\Core\SecurityContext
     */
    public function testHowToAccessAppInfoLikeSecuritycontextOrRequest()
    {
        $cli = $this->client;
        $crawler = $cli->request('GET', '/templating/twig/app');
        $body = json_decode($cli->getResponse()->getContent(), true);

        $this->assertEquals(true, $body['app.debug']);
        $this->assertEquals('test', $body['app.environment']);
        $this->assertEquals('Symfony\Component\HttpFoundation\Request', $body['app.request']);
        $this->assertEquals('', $body['app.user'], 'app.user allows to access the connected object user, here no connected so empty string given');
        $this->assertEquals('Symfony\Component\Security\Core\SecurityContext', $body['app.security']);
    }
}
