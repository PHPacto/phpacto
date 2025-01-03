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

namespace PHPacto\Factory;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

class SerializerFactoryTest extends TestCase
{
    public function test_it_returns_serializer()
    {
        self::assertInstanceOf(Serializer::class, SerializerFactory::getInstance());
    }
}
