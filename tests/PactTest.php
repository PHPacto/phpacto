<?php

namespace Bigfoot\PHPacto;

use Bigfoot\PHPacto\Factory\SerializerFactory;
use PHPUnit\Framework\TestCase;

class PactTest extends TestCase
{
    public function test_it_should_get_request_response()
    {
        $request = $this->createMock(PactRequest::class);
        $response = $this->createMock(PactResponse::class);

        $c = new Pact($request, $response, 'desc', 'ver');

        $this->assertSame($request, $c->getRequest());
        $this->assertSame($response, $c->getResponse());
        $this->assertEquals('desc', $c->getDescription());
        $this->assertEquals('ver', $c->getVersion());
    }

    public function test_it_throws_exception_if_version_is_unsupported()
    {
        $request = $this->createMock(PactRequest::class);
        $response = $this->createMock(PactResponse::class);

        self::expectExceptionMessage('Unsupported Pact version');
        new Pact($request, $response, 'desc', '1');
    }

    public function test_it_is_normalizable()
    {
        $request = $this->createMock(PactRequest::class);

        $response = $this->createMock(PactResponse::class);

        $pact = new Pact($request, $response, 'desc', 'ver');

        $expected = [
            'version' => 'ver',
            'description' => 'desc',
            'request' => [
                'method' => ['@rule' => get_class($request->getMethod()), 'value' => null],
                'uri' => ['@rule' => get_class($request->getUri()), 'value' => null],
            ],
            'response' => [
                'status_code' => ['@rule' => get_class($response->getStatusCode()), 'value' => null],
            ],
        ];

        $normalizer = SerializerFactory::getInstance();
        $this->assertSame($expected, $normalizer->normalize($pact));
    }
}
