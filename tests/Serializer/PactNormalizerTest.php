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

namespace PHPacto\Serializer;

use PHPacto\PactInterface;
use PHPacto\PactRequestInterface;
use PHPacto\PactResponseInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class PactNormalizerTest extends TestCase
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
    public function test_it_supports_normalization(?string $format)
    {
        /** @var PactNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(PactNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsNormalization', 'getSupportedTypes'])
            ->getMock();

        $pact = $this->createMock(PactInterface::class);

        self::assertTrue($normalizer->supportsNormalization($pact, $format));
    }

    /**
     * @dataProvider normalizationFormatProvider
     */
    public function test_it_supports_denormalization(?string $format)
    {
        /** @var PactNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(PactNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsDenormalization', 'getSupportedTypes'])
            ->getMock();

        self::assertTrue($normalizer->supportsDenormalization([], PactInterface::class, $format));
    }

    public function test_normalize()
    {
        $requestNormalizer = $this->getMockBuilder(PactRequestNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsNormalization', 'getSupportedTypes'])
            ->getMock();

        $responseNormalizer = $this->getMockBuilder(PactResponseNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsNormalization', 'getSupportedTypes'])
            ->getMock();

        $requestNormalizer
            ->expects(self::once())
            ->method('normalize')
            ->with(self::isInstanceOf(PactRequestInterface::class))
            ->willReturn(['Request']);

        $responseNormalizer
            ->expects(self::once())
            ->method('normalize')
            ->with(self::isInstanceOf(PactResponseInterface::class))
            ->willReturn(['Response']);

        $serializer = new Serializer(
            [new PactNormalizer(), $requestNormalizer, $responseNormalizer],
            [new JsonEncoder()]
        );

        $pact = $this->createMock(PactInterface::class);

        $data = $serializer->normalize($pact);

        $expected = [
            'version' => '',
            'description' => '',
            'request' => ['Request'],
            'response' => ['Response'],
        ];

        self::assertEquals($expected, $data);

        return $data;
    }

    public function test_denormalize()
    {
        $requestNormalizer = $this->getMockBuilder(PactRequestNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsDenormalization', 'getSupportedTypes'])
            ->getMock();

        $responseNormalizer = $this->getMockBuilder(PactResponseNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsDenormalization', 'getSupportedTypes'])
            ->getMock();

        $requestNormalizer
            ->expects(self::once())
            ->method('denormalize')
            ->willReturn($this->createMock(PactRequestInterface::class));

        $responseNormalizer
            ->expects(self::once())
            ->method('denormalize')
            ->willReturn($this->createMock(PactResponseInterface::class));

        $serializer = new Serializer(
            [new PactNormalizer(), $requestNormalizer, $responseNormalizer],
            [new JsonEncoder()]
        );

        $data = [
            'version' => '',
            'description' => '',
            'request' => ['Request'],
            'response' => ['Response'],
        ];

        $pact = $serializer->denormalize($data, PactInterface::class);

        self::assertInstanceOf(PactInterface::class, $pact);
    }
}
