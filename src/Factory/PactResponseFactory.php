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

namespace PHPacto\Factory;

use PHPacto\Matcher\Rules\EqualsRule;
use PHPacto\Matcher\Rules\Rule;
use PHPacto\PactResponse;
use PHPacto\PactResponseInterface;
use Psr\Http\Message\ResponseInterface;

abstract class PactResponseFactory extends PactMessageFactory
{
    public static function createFromPSR7(ResponseInterface $response): PactResponseInterface
    {
        $statusCode = self::getStatusCodeRule($response);
        $headers = self::getHeadersRules($response);
        $body = self::getBodyRules($response);

        return new PactResponse($statusCode, $headers, $body);
    }

    protected static function getStatusCodeRule(ResponseInterface $response): Rule
    {
        return new EqualsRule($response->getStatusCode());
    }
}
