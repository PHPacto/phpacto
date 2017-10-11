<?php

namespace Bigfoot\PHPacto\Controller;

use Bigfoot\PHPacto\Factory\PactRequestFactory;
use Bigfoot\PHPacto\Factory\PactResponseFactory;
use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Logger\Logger;
use Bigfoot\PHPacto\Pact;
use Bigfoot\PHPacto\PactInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class ProxyController
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * @var string
     */
    private $contractsDir;

    public function __construct(Client $client, Logger $logger, UriInterface $uri, string $contractsDir)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->uri = $uri;
        $this->contractsDir = $contractsDir;
    }

    public function action(RequestInterface $request): ResponseInterface
    {
        $pactRequest = PactRequestFactory::createFromPSR7($request);

        $request = $pactRequest->getSample();

        $response = $this->makeProxyCall($request);

        $pactResponse = PactResponseFactory::createFromPSR7($response);

        $dateStr = date('Y-m-d H:i:s');

        $pact = new Pact($pactRequest, $pactResponse, 'Created automatically - '.$dateStr);

        $this->createContractFile($pact, $dateStr);

        return $pactResponse->getSample();
    }

    public function makeProxyCall(RequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $headers = $request->getHeaders();
        $body = (string) $request->getBody();

        try {
            // Proxy the HTTP request
            return $this->client->request($method, $this->uri, [
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

    protected function createContractFile(PactInterface $pact, string $dateStr): void
    {
        $filename = sprintf('%s/%s %s.yaml', $this->contractsDir, $dateStr, floatval(microtime())*1000000);

        $serializer = SerializerFactory::getInstance();
        file_put_contents($filename, $serializer->serialize($pact, 'yaml'));

        $this->logger->log('Contract wrote to '.realpath($filename));
    }
}