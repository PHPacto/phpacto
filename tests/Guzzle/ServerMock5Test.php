<?php

namespace Bigfoot\PHPacto;

use Bigfoot\PHPacto\Guzzle\ServerMock5;
use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

/**
 * @group guzzle
 */
class ServerMock5Test extends TestCase
{
    /**
     * @var ServerMock5
     */
    private $server;

    public function setUp()
    {
        $guzzleVersion = \GuzzleHttp\ClientInterface::VERSION;

        if (version_compare($guzzleVersion, 5, '<') || version_compare($guzzleVersion, 6, '>=')) {
            self::markTestSkipped(sprintf('Incompatible Guzzle version (%s)', $guzzleVersion));
        }

        $this->server = new ServerMock5();
    }

    /**
     * @group guzzle5
     */
    public function test_it_throws_mismatch_if_request_not_match()
    {
        $request = $this->createMock(PactRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('assertMatch')
            ->willThrowException(new MismatchCollection([]));

        $pact = $this->createMock(PactInterface::class);
        $pact
            ->expects(self::atLeastOnce())
            ->method('getRequest')
            ->willReturn($request);

        $this->server->handlePact($pact);

        $client = new Client(['handler' => $this->server->getHandler()]);

        try {
            $client->get('/');
        } catch(\GuzzleHttp\Exception\RequestException $e) {
            self::assertInstanceOf(MismatchCollection::class, $e->getPrevious());

            return;
        }

        self::assertFalse(true, 'This test should end in the catch');
    }

    /**
     * @group guzzle5
     */
    public function test_it_match_request_and_respond_with_a_response_mock()
    {
        $request = $this->createMock(PactRequestInterface::class);
        $response = $this->createMock(PactResponseInterface::class);

        $pact = $this->createMock(PactInterface::class);
        $pact
            ->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);
        $pact
            ->expects(self::atLeastOnce())
            ->method('getResponse')
            ->willReturn($response);

        $request
            ->expects(self::once())
            ->method('assertMatch');

        $response
            ->expects(self::once())
            ->method('assertMatch');

        $responseBody = new Stream('php://memory', 'w');
        $responseBody->write('mock');

        $response
            ->expects(self::once())
            ->method('getSample')
            ->willReturn(new Response($responseBody, 123, []));

        $this->server->handlePact($pact);

        $client = new Client(['handler' => $this->server->getHandler()]);

        $resp = $client->get('/');

        self::assertEquals(123, $resp->getStatusCode());
        self::assertEquals('mock', (string) $resp->getBody());
    }
}
