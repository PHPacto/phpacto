<?php

namespace Bigfoot\PHPacto\Controller;

use Bigfoot\PHPacto\Logger\Logger;
use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\PactInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Uri;

class MockController
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $pacts;

    public function __construct(Logger  $logger, array $pacts)
    {
        $this->logger = $logger;
        $this->pacts = $pacts;
    }

    public function action(RequestInterface $request): ResponseInterface
    {
        $pact = $this->getPactMatchingRequest($request);

        $response = $pact->getResponse()->getSample();

        // Assert that response is matching rules
        $pact->getResponse()->assertMatch($response);

        return $response;
    }

    protected function getPactMatchingRequest(RequestInterface $request): PactInterface
    {
        $uri = (new Uri())
            ->withPath($request->getUri()->getPath())
            ->withQuery($request->getUri()->getQuery());

        $request = $request->withUri($uri);

        /** @var PactInterface $pact */
        foreach ($this->pacts as $filepath => $pact) {
            try {
                $pact->getRequest()->assertMatch($request);

                $this->logger->log(sprintf('Using contract from file %s', $filepath));

                return $pact;
            } catch (Mismatch $e) {
            }
        }

        throw new \Exception('No pact was found matching your request');
    }
}
