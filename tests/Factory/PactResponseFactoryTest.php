<?php

namespace Bigfoot\PHPacto\Factory;

use Bigfoot\PHPacto\PactResponse;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

class PactResponseFactoryTest extends TestCase
{
    public function test_it_returns_pact_response_minimal()
    {
        $response = new Response('php://memory', 200);

        $pactResponse = PactResponseFactory::createFromPSR7($response);

        self::assertInstanceOf(PactResponse::class, $pactResponse);
        self::assertEquals(200, $pactResponse->getStatusCode()->getValue());
        self::assertCount(0, $pactResponse->getHeaders());
        self::assertNull($pactResponse->getBody());
    }

    /**
     * @depends test_it_returns_pact_response_minimal
     */
    public function test_it_returns_pact_response_with_headers()
    {
        $response = new Response('php://memory', 200, ['X-CUSTOM' => 'a custom header']);

        $pactResponse = PactResponseFactory::createFromPSR7($response);

        self::assertContains('custom header', $pactResponse->getHeaders()['X-CUSTOM']->getValue());
    }

    /**
     * @depends test_it_returns_pact_response_minimal
     */
    public function test_it_returns_pact_response_with_body_plain_string()
    {
        $stream = new Stream('php://memory', 'w');
        $stream->write('some content');

        $response = new Response($stream, 200);

        $pactResponse = PactResponseFactory::createFromPSR7($response);

        self::assertEquals('some content', $pactResponse->getBody()->getValue());
    }

    /**
     * @depends test_it_returns_pact_response_with_body_plain_string
     */
    public function test_it_returns_pact_response_with_body_url_encoded()
    {
        $stream = new Stream('php://memory', 'w');
        $stream->write('a=1&b%5B0%5D=2&b%5B1%5D=3');

        $response = new Response($stream, 200, ['Content-Type' => 'application/x-www-form-urlencoded']);

        $pactResponse = PactResponseFactory::createFromPSR7($response);

        $expectedBody = [
            'a' => 1,
            'b' => [2.0, '3']
        ];

        self::assertEquals($expectedBody, $pactResponse->getBody()->getSample());
    }

    /**
     * @depends test_it_returns_pact_response_with_headers
     * @depends test_it_returns_pact_response_with_body_url_encoded
     */
    public function test_it_returns_pact_response_with_body_json_encoded()
    {
        $stream = new Stream('php://memory', 'w');
        $stream->write('{"a":1,"0":[2,"3"]}');

        $response = new Response($stream, 200, ['Content-Type' => 'application/json']);

        $pactResponse = PactResponseFactory::createFromPSR7($response);

        $expectedBody = [
            'a' => 1,
            0 => [2.0, '3']
        ];

        self::assertEquals($expectedBody, $pactResponse->getBody()->getSample());
    }
}
