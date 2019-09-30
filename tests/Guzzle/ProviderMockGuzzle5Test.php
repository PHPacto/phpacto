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

use Bigfoot\PHPacto\Guzzle\ProviderMockGuzzle5;
use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Http\Factory\Discovery\HttpFactory;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

/**
 * @group guzzle
 */
class ProviderMockGuzzle5Test extends TestCase
{
    /**
     * @var ProviderMockGuzzle5
     */
    private $server;

    public function setUp()
    {
        $guzzleVersion = ClientInterface::VERSION;

        if (version_compare($guzzleVersion, '5', '<') || version_compare($guzzleVersion, '6', '>=')) {
            self::markTestSkipped(sprintf('Incompatible Guzzle version (%s)', $guzzleVersion));
        }

        $this->server = new ProviderMockGuzzle5();
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
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            self::assertInstanceOf(AssertionFailedError::class, $e->getPrevious());
            self::assertRegExp('/Failed asserting that request `.*` matches Pact/', $e->getPrevious()->getMessage());

            return;
        }

        self::fail('This test should end in the catch');
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

        $responseBody = HttpFactory::streamFactory()->createStreamFromFile('php://memory', 'w');
        $responseBody->write('mock');

        $response
            ->expects(self::once())
            ->method('getSample')
            ->willReturn(HttpFactory::responseFactory()->createResponse(123)->withBody($responseBody));

        $this->server->handlePact($pact);

        $client = new Client(['handler' => $this->server->getHandler()]);

        $resp = $client->get('/');

        self::assertEquals(123, $resp->getStatusCode());
        self::assertEquals('mock', (string) $resp->getBody());
    }
}
