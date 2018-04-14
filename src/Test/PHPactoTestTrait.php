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

use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Guzzle;
use Bigfoot\PHPacto\Loader\FileLoader;
use Bigfoot\PHPacto\PactInterface;
use Bigfoot\PHPacto\Test\PHPUnit\Constraint\PactMatchesRequest;
use Bigfoot\PHPacto\Test\PHPUnit\Constraint\PactMatchesResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait PHPactoTestTrait
{
    protected function createServerMock(): Guzzle\ServerMock
    {
        $guzzleVersion = \GuzzleHttp\ClientInterface::VERSION;

        if (version_compare($guzzleVersion, 6, '<')) {
            return new Guzzle\ServerMock5();
        }

        elseif (version_compare($guzzleVersion, 7, '<')) {
            return new Guzzle\ServerMock6();
        }

        throw new \Exception('No valid Guzzle version is found. Please install Guzzle version 6 or 5.');
    }

    /**
     * @return FileLoader
     */
    private function getLoader(): FileLoader
    {
        return new FileLoader(SerializerFactory::getInstance());
    }

    /**
     * Load a contract and returns a Pact
     *
     * @param string $path
     * @return PactInterface
     */
    protected function loadContract(string $path): PactInterface
    {
        return $this->getLoader()->loadFromFile($path);
    }

    /**
     * Matches a Request against a Pact.
     *
     * @param PactInterface $pact
     * @param RequestInterface $request
     * @param string|null $message
     */
    public function assertPactMatchesRequest(PactInterface $pact, RequestInterface $request, string $message = null)
    {
        $constraint = new PactMatchesRequest($pact);

        static::assertThat($request, $constraint, $message);
    }

    /**
     * Matches a Response against a Pact.
     *
     * @param PactInterface $pact
     * @param ResponseInterface $response
     * @param string|null $message
     */
    public function assertPactMatchesResponse(PactInterface $pact, ResponseInterface $response, string $message = null)
    {
        $constraint = new PactMatchesResponse($pact);

        static::assertThat($response, $constraint, $message);
    }
}
