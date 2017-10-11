<?php

namespace Bigfoot\PHPacto\Matcher;

use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use Bigfoot\PHPacto\PactInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface PactMatcherInterface
{
    /**
     * Match the request with given pact
     *
     * @param PactInterface $pact
     * @param RequestInterface $request
     * @throws MismatchCollection
     */
    public function assertMatchRequest(PactInterface $pact, RequestInterface $request): void;

    /**
     * Match the request with given pact
     *
     * @param PactInterface $pact
     * @param ResponseInterface $response
     * @throws MismatchCollection
     */
    public function assertMatchResponse(PactInterface $pact, ResponseInterface $response): void;
}
