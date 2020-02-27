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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bigfoot\PHPacto\Guzzle;

use Bigfoot\PHPacto\PactInterface;
use Bigfoot\PHPacto\Test\PHPactoTestTrait;
use GuzzleHttp\Ring\Client\MockHandler;
use Http\Factory\Discovery\HttpFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ProviderMockGuzzle5 implements ProviderMock
{
    use PHPactoTestTrait;

    /**
     * @var MockHandler
     */
    private $mock;

    public function handlePact(PactInterface $pact): void
    {
        $this->mock = new MockHandler(function(array $requestArray) use ($pact): array {
            $request = self::getRequestFromArray($requestArray);

            self::assertRequestMatchesPact($pact, $request);

            $response = $pact->getResponse()->getSample();

            return self::responseToArray($response);
        });
    }

    /**
     * @return MockHandler
     */
    public function getHandler()
    {
        return $this->mock;
    }

    private function getRequestFromArray(array $request): RequestInterface
    {
        $uri = $request['uri'];
        $protocol = $request['version'];
        $method = $request['http_method'];
        $headers = $request['headers'];
        $body = $request['body'];

        $request = HttpFactory::serverRequestFactory()->createServerRequest($method, $uri)
            ->withProtocolVersion($protocol);

        if ($body) {
            $stream = HttpFactory::streamFactory()->createStreamFromFile('php://memory', 'w');
            $stream->write('mock');

            $request = $request->withBody($stream);
        }

        foreach ($headers as $key => $value) {
            $request = $request->withAddedHeader($key, $value);
        }

        return $request;
    }

    private static function responseToArray(ResponseInterface $response): array
    {
        return [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => (string) $response->getBody(),
        ];
    }
}
