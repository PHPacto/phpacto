<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2018  Damian Długosz
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
     * @var PactInterface[]
     */
    private $pacts;

    /**
     * @var string|null
     */
    private $allowOrigin;

    public function __construct(Logger  $logger, array $pacts, $allowOrigin = null)
    {
        $this->logger = $logger;
        $this->pacts = $pacts;
        $this->allowOrigin = $allowOrigin;
    }

    public function action(RequestInterface $request): ResponseInterface
    {
        $uri = (new Uri())
            ->withPath($request->getUri()->getPath())
            ->withQuery($request->getUri()->getQuery());

        $request = $request->withUri($uri);

        $pact = $this->findMatchingPact($request);

        $response = $pact->getResponse()->getSample();

        if (null !== $this->allowOrigin) {
            return $response
                ->withHeader('Access-Control-Allow-Credentials', 'True')
                ->withHeader('Access-Control-Allow-Headers', '*')
                ->withHeader('Access-Control-Allow-Origin', $this->allowOrigin);
        }

        return $response;
    }

    private function findMatchingPact(RequestInterface $request): PactInterface
    {
        foreach ($this->pacts as $contractLocation => $pact) {
            try {
                $pact->getRequest()->assertMatch($request);

                $this->logger->log(\sprintf('Found contract matching request %s', $contractLocation));

                return $pact;
            } catch (Mismatch $e) {
                // This Pact isn't matching, try next.
            }
        }

        throw new \Exception('Any contract found matching your request');
    }
}
