<?php

namespace Bigfoot\PHPacto\Factory;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

class SerializerFactoryTest extends TestCase
{
    public function test_it_returns_serializer()
    {
        self::assertInstanceOf(Serializer::class, SerializerFactory::getInstance());
    }
}
