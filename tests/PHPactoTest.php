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

use PHPacto\Guzzle\ProviderMock;
use PHPacto\Loader\PactLoader;
use PHPUnit\Framework\TestCase;

class PHPactoTest extends TestCase
{
    public function test_it_returns_server_mock()
    {
        $phpacto = new PHPacto();

        self::assertInstanceOf(ProviderMock::class, $phpacto->createServerMock());
    }

    public function test_it_returns_loader()
    {
        // PHPacto mock
        $phpacto = new class () extends PHPacto {
            // Mock getLoader() protected method
            public function getLoaderPublic()
            {
                return $this->getLoader();
            }
        };

        self::assertInstanceOf(PactLoader::class, $phpacto->getLoaderPublic());
    }

    public function test_it_calls_file_loader()
    {
        $base_path = 'base_path/';

        $loader = $this->createMock(PactLoader::class);

        // PHPacto mock
        $phpacto = new class ($base_path, $loader) extends PHPacto {
            public function __construct(string $contractsBasePath = null, PactLoader $loader)
            {
                parent::__construct($contractsBasePath);

                $this->loader = $loader;
            }

            // Mock getLoader() protected method
            // Return mocked PactLoader
            protected function getLoader(): PactLoader
            {
                return $this->loader;
            }
        };

        $loader
            ->expects(self::once())
            ->method('loadFromFile')
            ->with('base_path/file.json');

        $pact = $phpacto->getPact('file.json');

        self::assertInstanceOf(PactInterface::class, $pact);
    }
}
