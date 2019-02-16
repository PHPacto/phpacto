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

namespace Bigfoot\PHPacto;

use Bigfoot\PHPacto\Guzzle\ProviderMockGuzzle6;
use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use GuzzleHttp\Client;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @group guzzle
 */
class ProviderMockGuzzle6Test extends TestCase
{
    /**
     * @var ProviderMockGuzzle6
     */
    private $server;

    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $guzzleVersion = \GuzzleHttp\ClientInterface::VERSION;

        if (version_compare($guzzleVersion, '6', '<') || version_compare($guzzleVersion, '7', '>=')) {
            self::markTestSkipped(sprintf('Incompatible Guzzle version (%s)', $guzzleVersion));
        }

        $this->server = new ProviderMockGuzzle6();
        $this->client = new Client(['handler' => $this->server->getHandler()]);
    }

    /**
     * @group guzzle6
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
            ->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $this->server->handlePact($pact);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp('/Failed asserting that request `.*` matches Pact/');

        $this->client->request('GET', '/');
    }

    /**
     * @group guzzle6
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
            ->method('getSample')
            ->willReturn($psr7Response = $this->createMock(ResponseInterface::class));

        $this->server->handlePact($pact);

        $resp = $this->client->request('GET', '/');
        self::assertSame($psr7Response, $resp);
    }
}
