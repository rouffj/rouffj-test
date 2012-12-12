<?php

namespace Rouffj\Tests\Symfony\Controller;

use Rouffj\Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ControllerTest extends TestCase
{
    public function doSetUp()
    {
    }

    public function testHowToReadQueryFromRequest()
    {
        $cli = $this->client;
        $crawler = $cli->request('GET', '/controller/query?param1=valueParam1');
        $body = json_decode($cli->getResponse()->getContent(), true);

        $this->assertEquals('valueParam1', $body['param1']);
        $this->assertEquals('defaultValueParam2', $body['param2'], 'param named "param2" is not passed to the query, but the action defined a default value');
        $this->assertEquals(null, $body['param3'], 'param named "param3" does not exists, in this case null is the value');
        $this->assertSame(true, $body['param1_defined']);
    }

    public function testHowToReadServerInfoFromRequest()
    {
        $crawler = $this->client->request('GET', '/controller/server');
        $body = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('localhost', $body['server_name']);
        $this->assertEquals('HTTP/1.1', $body['server_protocol']);
    }

    public function testHowToReadWriteSession()
    {
        $crawler = $this->client->request('GET', '/controller/create_session');

        $crawler = $this->client->request('GET', '/controller/read_session');
        $body = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(true, $body['sessionA_defined'], 'With this second request we see that the session, once created, is persistent between request');

        $crawler = $this->client->request('GET', '/controller/read_session');
        $body = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(true, $body['sessionA_defined'], 'Third request, session still here.');
    }

    public function testHowToReadWriteFlashes()
    {
        $this->assertCount(0, $this->client->getCookieJar()->all());
        $crawler = $this->client->request('POST', '/controller/flash');
        $this->assertCount(1, $this->client->getCookieJar()->all(), 'after the flash creation a cookie with the session identifier is sent back to the HTTP client');

        $crawler = $this->client->request('GET', '/controller/flash');
        $body = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $body['notice_flashes'], 'With this second request we see that the session, once created, is persistent between request');
        $this->assertCount(1, $body['error_flashes'], 'Session is present');

        $crawler = $this->client->request('GET', '/controller/flash');
        $body = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(0, $body['notice_flashes'], 'Second GET request, no more flashes, because FlashBag#get delete flashes.');
        $this->assertCount(1, $body['error_flashes'], 'Flashes are still present because FlashBag#peek does NOT delete flashes.');
    }

    public function testHowToGenerate404Page()
    {
        try {
            $crawler = $this->client->request('GET', '/controller/404');
            $this->fail();
        } catch(NotFoundHttpException $e) {
            $this->assertEquals('The product does not exist', $e->getMessage());
        }
    }

    /**
     * - The target of a Controller#forward is not required to be a route, it can be just of regular method which return a Response.
     * - forward behaves same as render twig tag
     */
    public function testHowToMakeForward()
    {
        $this->client->request('GET', '/controller/forward');
        $body = $this->client->getResponse()->getContent();
        $this->assertEquals('forwardedAction.value1.value2.queryValue1', $body);
    }

    public function testHowToGenerateRedirectionResponse()
    {
        $this->client->request('GET', '/controller/temporary-redirection');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), 'By default Controller#redirect generate a temporary redirection');
        $this->assertEquals(true, $this->client->getResponse()->isRedirection());

        $this->client->request('GET', '/controller/permanent-redirection');
        $this->assertEquals(301, $this->client->getResponse()->getStatusCode(), 'To force permanent redirect we have to pass 301 as second param');
        $this->assertEquals(true, $this->client->getResponse()->isRedirection());
    }
}
