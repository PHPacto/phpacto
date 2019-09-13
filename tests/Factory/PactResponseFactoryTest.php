<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2019  Damian Długosz
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
        self::assertEquals(200, $pactResponse->getStatusCode()->getSample());
        self::assertCount(0, $pactResponse->getHeaders());
        self::assertNull($pactResponse->getBody());
    }

    /**
     * @depends test_it_returns_pact_response_minimal
     */
    public function test_it_returns_pact_response_with_headers()
    {
        $response = new Response('php://memory', 200, ['x-CUSTOM' => 'a custom header']);

        $pactResponse = PactResponseFactory::createFromPSR7($response);

        self::assertContains('custom header', $pactResponse->getHeaders()['X-Custom']->getSample());
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

        self::assertEquals('some content', $pactResponse->getBody()->getSample());
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
            'b' => [2.0, '3'],
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
            0 => [2.0, '3'],
        ];

        self::assertEquals($expectedBody, $pactResponse->getBody()->getSample());
    }

    /**
     * @depends test_it_returns_pact_response_with_headers
     * @depends test_it_returns_pact_response_with_body_plain_string
     */
    public function test_it_returns_pact_response_with_content_type_mime_and_charset()
    {
        $stream = new Stream('php://memory', 'w');
        $stream->write('{"a":1,"0":[2,"3"]}');

        $response = new Response($stream, 200, ['Content-Type' => 'application/json; charset=UTF-8']);

        $pactResponse = PactResponseFactory::createFromPSR7($response);

        $expectedBody = [
            'a' => 1,
            0 => [2.0, '3'],
        ];

        self::assertEquals($expectedBody, $pactResponse->getBody()->getSample());
    }
}
