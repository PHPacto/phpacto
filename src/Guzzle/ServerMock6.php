<?php

/*
 * This file is part of PHPacto
 * Copyright (C) 2017  Damian Długosz
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

namespace Bigfoot\PHPacto\Guzzle;

use Bigfoot\PHPacto\PactInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServerMock6 implements ServerMock
{
    /**
     * @var MockHandler
     */
    private $mock;

    public function __construct()
    {
        $this->mock = new MockHandler();
    }

    public function handlePact(PactInterface $pact): void
    {
        $this->mock->append(function (RequestInterface $request) use ($pact): ResponseInterface {
            $pact->getRequest()->assertMatch($request);

            $response = $pact->getResponse()->getSample();

            // Assert that response is matching rules
            $pact->getResponse()->assertMatch($response);

            return $response;
        });
    }

    /**
     * @return HandlerStack
     */
    public function getHandler(): HandlerStack
    {
        return HandlerStack::create($this->mock);
    }
}
