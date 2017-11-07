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

namespace Bigfoot\PHPacto\Controller;

use Bigfoot\PHPacto\Logger\Logger;
use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\PactInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Uri;

class MockController
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $pacts;

    public function __construct(Logger  $logger, array $pacts)
    {
        $this->logger = $logger;
        $this->pacts = $pacts;
    }

    public function action(RequestInterface $request): ResponseInterface
    {
        $pact = $this->getPactMatchingRequest($request);

        $response = $pact->getResponse()->getSample();

        // Assert that response is matching rules
        $pact->getResponse()->assertMatch($response);

        return $response;
    }

    protected function getPactMatchingRequest(RequestInterface $request): PactInterface
    {
        $uri = (new Uri())
            ->withPath($request->getUri()->getPath())
            ->withQuery($request->getUri()->getQuery());

        $request = $request->withUri($uri);

        /** @var PactInterface $pact */
        foreach ($this->pacts as $filepath => $pact) {
            try {
                $pact->getRequest()->assertMatch($request);

                $this->logger->log(sprintf('Using contract from file %s', $filepath));

                return $pact;
            } catch (Mismatch $e) {
                // TODO: Implement errors displaying instead of a blank page
            }
        }

        throw new \Exception('No pact was found matching your request');
    }
}
