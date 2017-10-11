<?php

namespace Bigfoot\PHPacto\Guzzle;

use Bigfoot\PHPacto\PactInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServerMock6 implements ServerMock
{
    /**
     * @var MockHandler
     */
    private $mock;

    public function __construct()
    {
        $this->mock = new MockHandler();
    }

    public function handlePact(PactInterface $pact): void
    {
        $this->mock->append(function (RequestInterface $request) use ($pact): ResponseInterface {
            $pact->getRequest()->assertMatch($request);

            $response = $pact->getResponse()->getSample();

            // Assert that response is matching rules
            $pact->getResponse()->assertMatch($response);

            return $response;
        });
    }

    /**
     * @return HandlerStack
     */
    public function getHandler(): HandlerStack
    {
        return HandlerStack::create($this->mock);
    }
}
