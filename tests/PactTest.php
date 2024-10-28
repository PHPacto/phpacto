<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) Damian Długosz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 */

namespace PHPacto;

use PHPacto\Serializer\SerializerAwareTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PactTest extends SerializerAwareTestCase
{
    public function test_it_should_get_request_response()
    {
        $request = $this->createMock(PactRequest::class);
        $response = $this->createMock(PactResponse::class);

        $c = new Pact($request, $response, 'desc', 'ver');

        self::assertSame($request, $c->getRequest());
        self::assertSame($response, $c->getResponse());
        self::assertEquals('desc', $c->getDescription());
        self::assertEquals('ver', $c->getVersion());
    }

    public function test_it_throws_exception_if_version_is_unsupported()
    {
        $request = $this->createMock(PactRequest::class);
        $response = $this->createMock(PactResponse::class);

        $this->expectExceptionMessage('Unsupported Pact version');
        new Pact($request, $response, 'desc', '1');
    }

    public function test_it_is_normalizable()
    {
        $request = $this->createMock(PactRequest::class);

        $response = $this->createMock(PactResponse::class);

        $pact = new Pact($request, $response, 'desc', 'ver');

        $this->rule->map($request->getMethod());
        $this->rule->map($request->getPath());
        $this->rule->map($response->getStatusCode());

        $expected = [
            'version' => 'ver',
            'description' => 'desc',
            'request' => [
                'method' => ['_rule' => \get_class($request->getMethod())],
                'path' => ['_rule' => \get_class($request->getPath())],
            ],
            'response' => [
                'status_code' => ['_rule' => \get_class($response->getStatusCode())],
            ],
        ];

        self::assertSame($expected, $this->normalizer->normalize($pact));
    }

    public function test_it_is_denormalizable()
    {
        $data = [
            'version' => 'ver',
            'description' => 'desc',
            'request' => [
                'method' => 'get',
                'path' => '/',
            ],
            'response' => [
                'status_code' => 200,
            ],
        ];

        $pact = $this->normalizer->denormalize($data, PactInterface::class);

        self::assertInstanceOf(Pact::class, $pact);
    }

    public function test_that_request_is_matching_its_rules_when_instantiating()
    {
        $request = $this->getMockBuilder(PactRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request
            ->expects(self::once())
            ->method('getSample')
            ->willReturn($sampleRequest = $this->createMock(ServerRequestInterface::class));

        $request
            ->expects(self::once())
            ->method('assertMatch')
            ->with($sampleRequest);

        $response = $this->getMockBuilder(PactResponse::class)
            ->disableOriginalConstructor()
            ->getMock();

        new Pact($request, $response);
    }

    public function test_that_response_is_matching_its_rules_when_instantiating()
    {
        $request = $this->getMockBuilder(PactRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response = $this->getMockBuilder(PactResponse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response
            ->expects(self::once())
            ->method('getSample')
            ->willReturn($sampleResponse = $this->createMock(ResponseInterface::class));

        $response
            ->expects(self::once())
            ->method('assertMatch')
            ->with($this->isInstanceOf(ResponseInterface::class));

        new Pact($request, $response);
    }
}
