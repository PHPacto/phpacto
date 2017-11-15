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

namespace Bigfoot\PHPacto\Serializer;

use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Bigfoot\PHPacto\PactRequestInterface;
use PHPUnit\Framework\TestCase;

class PactRequestNormalizerTest extends TestCase
{
    public function normalizationFormatProvider()
    {
        return [
            [null],
            ['json'],
            ['yaml'],
        ];
    }

    /**
     * @dataProvider normalizationFormatProvider
     */
    public function test_it_support_normalization(?string $format)
    {
        /** @var PactRequestNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(PactRequestNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsNormalization'])
            ->getMock();

        $pact = $this->createMock(PactRequestInterface::class);

        self::assertTrue($normalizer->supportsNormalization($pact, $format));
    }

    /**
     * @dataProvider normalizationFormatProvider
     */
    public function test_it_support_denormalization(?string $format)
    {
        /** @var PactRequestNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(PactRequestNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsDenormalization'])
            ->getMock();

        self::assertTrue($normalizer->supportsDenormalization([], PactRequestInterface::class, $format));
    }

    public function test_serialize()
    {
        $serializer = SerializerFactory::getInstance();

        $request = $this->createMock(PactRequestInterface::class);

        $expected = [
            'method' => ['@rule' => get_class($request->getMethod())],
            'uri' => ['@rule' => get_class($request->getUri())],
        ];

        self::assertEquals($expected, $serializer->normalize($request, 'json'));
    }

    public function test_deserialize()
    {
        $serializer = SerializerFactory::getInstance();

        $data = [
            'method' => 'get',
            'uri' => '/uri',
        ];

        /** @var PactRequestInterface $pact */
        $pact = $serializer->denormalize($data, PactRequestInterface::class, 'json');

        self::assertInstanceOf(PactRequestInterface::class, $pact);
        self::assertEquals('GET', $pact->getMethod()->getSample());
        self::assertEquals('/uri', $pact->getUri()->getSample());
    }
}
