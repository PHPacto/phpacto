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

namespace Bigfoot\PHPacto\Controller;

use Bigfoot\PHPacto\Factory\PactRequestFactory;
use Bigfoot\PHPacto\Factory\PactResponseFactory;
use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Logger\Logger;
use Bigfoot\PHPacto\Pact;
use Bigfoot\PHPacto\PactInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Uri;

class MockProxyController
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $proxyTo;

    /**
     * @var string
     */
    private $contractsDir;

    public function __construct(ClientInterface $client, Logger $logger, string $proxyTo, string $contractsDir)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->proxyTo = parse_url($proxyTo);
        $this->contractsDir = $contractsDir;
    }

    public function action(RequestInterface $request): ResponseInterface
    {
        $response = $this->makeProxyCall($request);

        $pactResponse = PactResponseFactory::createFromPSR7($response);

        $pactRequest = PactRequestFactory::createFromPSR7($request);

        $dateStr = date('Y-m-d H:i:s');

        $pact = new Pact($pactRequest, $pactResponse, 'Created automatically - '.$dateStr);

        $this->createContractFile($pact, $dateStr);

        return $pactResponse->getSample();
    }

    private function makeProxyCall(RequestInterface $request): ResponseInterface
    {
        $uri = $this->getProxiedUri($request->getUri());
        $method = $request->getMethod();
        $headers = $request->getHeaders();
        $body = (string) $request->getBody();

        try {
            // Proxy the HTTP request
            return $this->client->request($method, $uri, [
                'headers' => $headers,
                'body' => $body ?: null,
                'allow_redirects' => false,
            ]);
        } catch (RequestException $e) {
            if (!$e->hasResponse()) {
                throw $e;
            }

            return $e->getResponse();
        }
    }

    private function createContractFile(PactInterface $pact, string $dateStr): void
    {
        $filename = sprintf('%s/%s %s.yaml', $this->contractsDir, $dateStr, (float) (microtime()) * 1000000);

        $serializer = SerializerFactory::getInstance();
        file_put_contents($filename, $serializer->serialize($pact, 'yaml'));

        $this->logger->log('Contract wrote to '.realpath($filename));
    }

    private function getProxiedUri(UriInterface $uri): UriInterface
    {
        return (new Uri())
            ->withScheme($this->proxyTo['scheme'] ?? 'http')
            ->withHost($this->proxyTo['host'] ?? 'localhost')
            ->withPort($this->proxyTo['port'] ?? (@$this->proxyTo['scheme'] === 'https' ? 443 : 80))
            ->withPath(str_replace('//', '/', $this->proxyTo['path'] ?? '/') . $uri->getPath())
            ->withQuery($uri->getQuery())
            ->withFragment($uri->getFragment());
    }
}
