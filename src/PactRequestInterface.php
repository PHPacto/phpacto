<?php

namespace Bigfoot\PHPacto;

use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Psr\Http\Message\RequestInterface;

interface PactRequestInterface
{
    /**
     * @return Rule
     */
    public function getMethod(): Rule;

    /**
     * @return Rule
     */
    public function getUri(): Rule;

    /**
     * @return Rule[]
     */
    public function getHeaders(): array;

    /**
     * @return Rule|Rule[]|null
     */
    public function getBody();

    /**
     * Get PSR7 Request sample
     */
    public function getSample(): RequestInterface;

    /**
     * Match against a PSR7 Request
     *
     * @throws Matcher\Mismatches\MismatchCollection if not matching
     */
    public function assertMatch(RequestInterface $request);
}
