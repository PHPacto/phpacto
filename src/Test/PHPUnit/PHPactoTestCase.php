<?php

/*
 * This file is part of PHPacto
 *
 * Copyright (c) 2017  Damian Długosz <bigfootdd@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bigfoot\PHPacto\Test\PHPUnit;

use Bigfoot\PHPacto\PactInterface;
use Bigfoot\PHPacto\Test\PHPactoTestTrait;
use Bigfoot\PHPacto\Test\PHPUnit\Constraint\PactMatchesRequest;
use Bigfoot\PHPacto\Test\PHPUnit\Constraint\PactMatchesResponse;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Blacklist;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class PHPactoTestCase extends TestCase
{
    use PHPactoTestTrait;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        Blacklist::$blacklistedClassNames[__CLASS__] = 1;
    }

    public function assertPactMatchesRequest(PactInterface $pact, RequestInterface $request, string $message = null)
    {
        $constraint = new PactMatchesRequest($pact);

        static::assertThat($request, $constraint, $message);
    }

    public function assertPactMatchesResponse(PactInterface $pact, ResponseInterface $response, string $message = null)
    {
        $constraint = new PactMatchesResponse($pact);

        static::assertThat($response, $constraint, $message);
    }
}
