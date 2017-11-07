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

namespace Bigfoot\PHPacto\Test;

use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Guzzle;
use Bigfoot\PHPacto\Loader\FileLoader;
use Bigfoot\PHPacto\PactInterface;

trait PhpactoTestTrait
{
    protected function createServerMock(): Guzzle\ServerMock
    {
        $guzzleVersion = \GuzzleHttp\ClientInterface::VERSION;

        if (version_compare($guzzleVersion, 6, '<')) {
            return new Guzzle\ServerMock5();
        }

        return new Guzzle\ServerMock6();
    }

    protected function loadPact($path): PactInterface
    {
        return $this->getLoader()->loadFromFile($path);
    }

    private function getLoader(): FileLoader
    {
        return new FileLoader(SerializerFactory::getInstance());
    }
}
