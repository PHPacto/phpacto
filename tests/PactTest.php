<?php

/*
 * This file is part of PHPacto
 * Copyright (C) 2017  Damian Długosz
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
