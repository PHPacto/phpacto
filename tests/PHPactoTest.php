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

use Bigfoot\PHPacto\Guzzle\ServerMock;
use Bigfoot\PHPacto\Loader\FileLoader;
use PHPUnit\Framework\TestCase;

class PHPactoTest extends TestCase
{
    public function test_it_returns_server_mock()
    {
        $phpacto = new PHPacto();

        self::assertInstanceOf(ServerMock::class, $phpacto->createServerMock());
    }

    public function test_it_calls_file_loader()
    {
        $base_path = 'base_path/';

        $loader = $this->createMock(FileLoader::class);

        // Create PHPacto mock
        $phpacto = new class($base_path, $loader) extends PHPacto {
            public function __construct(string $contractsBasePath = null, FileLoader $loader)
            {
                parent::__construct($contractsBasePath);

                $this->loader = $loader;
            }

            // Mock getLoader() protected method
            // Return mocked FileLoader
            protected function getLoader(): FileLoader
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
