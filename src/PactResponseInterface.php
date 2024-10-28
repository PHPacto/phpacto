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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 */

namespace PHPacto;

use PHPacto\Matcher\Rules\Rule;
use Psr\Http\Message\ResponseInterface;

interface PactResponseInterface extends PactMessageInterface
{
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
     * Get PSR7 Response sample.
     */
    public function getSample(): ResponseInterface;

    /**
     * Match against a PSR7 Response.
     *
     * @throws Matcher\Mismatches\MismatchCollection if not matching
     */
    public function assertMatch(ResponseInterface $response);
}
