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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

interface PactRequestInterface extends PactMessageInterface
{
    public function getMethod(): Rule;

    public function getPath(): Rule;

    /**
     * @return Rule[]
     */
    public function getHeaders(): array;

    /**
     * @return Rule|Rule[]|null
     */
    public function getBody();

    /**
     * Get PSR7 Request sample.
     */
    public function getSample(): ServerRequestInterface;

    /**
     * Match against a PSR7 Request.
     *
     * @throws Matcher\Mismatches\Mismatch if not matching
     */
    public function assertMatch(RequestInterface $request);
}
