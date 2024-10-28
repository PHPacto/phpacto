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
use PHPacto\Matcher\Rules\StringEqualsRule;
use PHPacto\PactRequest;
use PHPacto\PactRequestInterface;
use Psr\Http\Message\RequestInterface;

abstract class PactRequestFactory extends PactMessageFactory
{
    public static function createFromPSR7(RequestInterface $request): PactRequestInterface
    {
        $method = self::getMethodRule($request);
        $uri = self::getUriRule($request);
        $headers = self::getHeadersRules($request);
        $body = self::getBodyRules($request);

        return new PactRequest($method, $uri, $headers, $body);
    }

    protected static function getMethodRule(RequestInterface $request): Rule
    {
        return new StringEqualsRule(strtoupper($request->getMethod()));
    }

    protected static function getUriRule(RequestInterface $request): Rule
    {
        $uri = $request->getUri()->getPath();

        if ($request->getUri()->getQuery()) {
            $uri .= $request->getUri()->getQuery();
        }

        return new StringEqualsRule($uri);
    }
}
