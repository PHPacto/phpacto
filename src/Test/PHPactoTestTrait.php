<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2017  Damian Długosz
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

namespace Bigfoot\PHPacto\Test;

use Bigfoot\PHPacto\PactInterface;
use Bigfoot\PHPacto\Test\PHPUnit\Constraint\RequestMatchesPact;
use Bigfoot\PHPacto\Test\PHPUnit\Constraint\ResponseMatchesPact;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait PHPactoTestTrait
{
    /**
     * Matches a Request against a Pact.
     *
     * @param PactInterface    $pact
     * @param RequestInterface $request
     * @param string|null      $message
     */
    public static function assertRequestMatchesPact(PactInterface $pact, RequestInterface $request, string $message = '')
    {
        $constraint = new RequestMatchesPact($pact);

        Assert::assertThat($request, $constraint, $message);
    }

    /**
     * Matches a Response against a Pact.
     *
     * @param PactInterface     $pact
     * @param ResponseInterface $response
     * @param string|null       $message
     */
    public static function assertResponseMatchesPact(PactInterface $pact, ResponseInterface $response, string $message = '')
    {
        $constraint = new ResponseMatchesPact($pact);

        Assert::assertThat($response, $constraint, $message);
    }
}
