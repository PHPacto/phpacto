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

use Bigfoot\PHPacto\Guzzle\ServerMock;
use Bigfoot\PHPacto\Loader\FileLoader;
use Bigfoot\PHPacto\PactInterface;
use PHPUnit\Framework\TestCase;

class PHPactoTestTraitTest extends TestCase
{
    /**
     * @var PHPactoTestTrait
     */
    private $trait;

    /**
     * @var FileLoader
     */
    private $loader;

    protected function setUp()
    {
        $loader = $this->loader = $this->createMock(FileLoader::class);

        $this->trait = new class($loader) {
            use PHPactoTestTrait;

            public function __construct(FileLoader $loader)
            {
                $this->loader = $loader;
            }

            // Mock getLoader() protected method
            // Return mocked FileLoader
            protected function getLoader(): FileLoader
            {
                return $this->loader;
            }
        };
    }

    public function test_it_returns_server_mock()
    {
        $method = new \ReflectionMethod($this->trait, 'createServerMock');
        $method->setAccessible(true);
        $server = $method->invoke($this->trait);

        self::assertInstanceOf(ServerMock::class, $server);
    }

    public function test_it_calls_file_loader()
    {
        $this->loader
            ->expects(self::once())
            ->method('loadFromFile');

        $method = new \ReflectionMethod($this->trait, 'loadPact');
        $method->setAccessible(true);
        $pact = $method->invoke($this->trait, '/file.json');

        self::assertInstanceOf(PactInterface::class, $pact);
    }
}
