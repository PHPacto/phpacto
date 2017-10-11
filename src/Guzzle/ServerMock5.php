<?php

namespace Bigfoot\PHPacto\Guzzle;

use Bigfoot\PHPacto\PactInterface;
use GuzzleHttp\Ring\Client\MockHandler;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Stream;

class ServerMock5 implements ServerMock
{
    /**
     * @var MockHandler
     */
    private $mock;

    public function handlePact(PactInterface $pact): void
    {
        $this->mock = new MockHandler(function (array $requestArray) use ($pact): array {
            $request = self::getRequestFromArray($requestArray);

            $pact->getRequest()->assertMatch($request);

            $response = $pact->getResponse()->getSample();

            // Assert that response is matching rules
            $pact->getResponse()->assertMatch($response);

            return self::responseToArray($response);
        });
    }

    private static function getRequestFromArray(array $request): RequestInterface
    {
        $uri = $request['uri'];
        $method = $request['http_method'];
        $headers = $request['headers'];

        $body = new Stream('php://memory', 'w');
        $body->write($request['body']);

        return new Request($uri, $method, $body, $headers);
    }

    private static function responseToArray(ResponseInterface $response): array
    {
        return [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => (string) $response->getBody()
        ];
    }

    /**
     * @return HandlerStack
     */
    public function getHandler()
    {
        return $this->mock;
    }
}
