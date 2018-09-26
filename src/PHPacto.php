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

namespace Bigfoot\PHPacto;

use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Loader\PactLoader;

class PHPacto
{
    /**
     * @var string
     */
    private $contractsBasePath;

    /**
     * @param string $contractsBasePath
     */
    public function __construct(string $contractsBasePath = '')
    {
        $this->contractsBasePath = rtrim($contractsBasePath, \DIRECTORY_SEPARATOR).\DIRECTORY_SEPARATOR;
    }

    public function createServerMock(): Guzzle\ServerMock
    {
        $guzzleVersion = \GuzzleHttp\ClientInterface::VERSION;

        if (version_compare($guzzleVersion, '6', '<')) {
            return new Guzzle\ServerMock5();
        }

        if (version_compare($guzzleVersion, '7', '<')) {
            return new Guzzle\ServerMock6();
        }

        throw new \Exception('No valid Guzzle version is found. Please install Guzzle version 6 or 5.');
    }

    /**
     * Load a contract file and returns a Pactgit.
     *
     * @param string $path
     *
     * @return PactInterface
     */
    public function getPact(string $path): PactInterface
    {
        return $this->getLoader()->loadFromFile($this->contractsBasePath.$path);
    }

    /**
     * @return PactLoader
     */
    protected function getLoader(): PactLoader
    {
        return new PactLoader(SerializerFactory::getInstance());
    }
}
