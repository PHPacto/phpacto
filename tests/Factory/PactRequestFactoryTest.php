<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2017  Damian Długosz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bigfoot\PHPacto\Factory;

use Bigfoot\PHPacto\PactRequest;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Request;
use Zend\Diactoros\Stream;

class PactRequestFactoryTest extends TestCase
{
    public function test_it_returns_pact_request_minimal()
    {
        $request = new Request('/path', 'get');

        $pactRequest = PactRequestFactory::createFromPSR7($request);

        self::assertInstanceOf(PactRequest::class, $pactRequest);
        self::assertEquals('GET', $pactRequest->getMethod()->getSample());
        self::assertEquals('/path', $pactRequest->getPath()->getSample());
        self::assertCount(0, $pactRequest->getHeaders());
        self::assertNull($pactRequest->getBody());
    }

    /**
     * @depends test_it_returns_pact_request_minimal
     */
    public function test_it_returns_pact_request_with_headers()
    {
        $request = new Request('/', 'get', 'php://memory', ['X-CUSTOM' => 'a custom header']);

        $pactRequest = PactRequestFactory::createFromPSR7($request);

        self::assertContains('custom header', $pactRequest->getHeaders()['X-CUSTOM']->getSample());
    }

    /**
     * @depends test_it_returns_pact_request_minimal
     */
    public function test_it_returns_pact_request_with_body_plain_string()
    {
        $stream = new Stream('php://memory', 'w');
        $stream->write('some content');

        $request = new Request('/', 'get', $stream);

        $pactRequest = PactRequestFactory::createFromPSR7($request);

        self::assertEquals('some content', $pactRequest->getBody()->getSample());
    }

    /**
     * @depends test_it_returns_pact_request_with_body_plain_string
     */
    public function test_it_returns_pact_request_with_body_url_encoded()
    {
        $stream = new Stream('php://memory', 'w');
        $stream->write('a=1&b%5B0%5D=2.1&b%5B1%5D=3');

        $request = new Request('/', 'get', $stream, ['Content-Type' => 'application/x-www-form-urlencoded']);

        $pactRequest = PactRequestFactory::createFromPSR7($request);

        $expectedBody = [
            'a' => 1,
            'b' => [2.1, '3'],
        ];

        self::assertEquals($expectedBody, $pactRequest->getBody()->getSample());
    }

    /**
     * @depends test_it_returns_pact_request_with_headers
     * @depends test_it_returns_pact_request_with_body_url_encoded
     */
    public function test_it_returns_pact_request_with_body_json_encoded()
    {
        $stream = new Stream('php://memory', 'w');
        $stream->write('{"a":1,"0":[2,"3"]}');

        $request = new Request('/', 'get', $stream, ['Content-Type' => 'application/json']);

        $pactRequest = PactRequestFactory::createFromPSR7($request);

        $expectedBody = [
            'a' => 1,
            0 => [2.0, '3'],
        ];

        self::assertEquals($expectedBody, $pactRequest->getBody()->getSample());
    }
}
