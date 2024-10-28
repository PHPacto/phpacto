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

use PHPacto\Factory\SerializerFactory;
use PHPacto\Loader\PactLoader;
use GuzzleHttp\ClientInterface;

class PHPacto
{
    /**
     * @var string
     */
    private $contractsBasePath;

    public function __construct(string $contractsBasePath = '')
    {
        $this->contractsBasePath = rtrim($contractsBasePath, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
    }

    public function createServerMock(): Guzzle\ProviderMock
    {
        if (!interface_exists(ClientInterface::class)) {
            throw new \Exception('Guzzle dependency missing');
        }

        if (version_compare(ClientInterface::VERSION, '8', '<')) {
            return new Guzzle\ProviderMockGuzzle6();
        }

        throw new \Exception('No valid Guzzle version is found. Please install Guzzle version 7, 6 or 5.');
    }

    /**
     * Load a contract file and returns a Pactgit.
     */
    public function getPact(string $path): PactInterface
    {
        return $this->getLoader()->loadFromFile($this->contractsBasePath . $path);
    }

    protected function getLoader(): PactLoader
    {
        return new PactLoader(SerializerFactory::getInstance());
    }
}
