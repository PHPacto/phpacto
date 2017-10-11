<?php

namespace Bigfoot\PHPacto;

use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Psr\Http\Message\ResponseInterface;

interface PactResponseInterface
{
    /**
     * @return Rule
     */
    public function getStatusCode(): Rule;

    /**
     * @return Rule[]
     */
    public function getHeaders(): array;

    /**
     * @return Rule|Rule[]|null
     */
    public function getBody();

    /**
     * Get PSR7 Response sample
     */
    public function getSample(): ResponseInterface;

    /**
     * Match against a PSR7 Response
     *
     * @throws Matcher\Mismatches\MismatchCollection if not matching
     */
    public function assertMatch(ResponseInterface $response);
}
