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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace PHPacto\Serializer;

use PHPacto\Factory\SerializerFactory;
use PHPacto\Matcher\Rules\StringRule;
use PHPacto\PactResponseInterface;

class PactResponseNormalizerTest extends SerializerAwareTestCase
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
        /** @var PactResponseNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(PactResponseNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsNormalization'])
            ->getMock();

        $pact = $this->createMock(PactResponseInterface::class);

        self::assertTrue($normalizer->supportsNormalization($pact, $format));
    }

    /**
     * @dataProvider normalizationFormatProvider
     */
    public function test_it_supports_denormalization(?string $format)
    {
        /** @var PactResponseNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(PactResponseNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsDenormalization'])
            ->getMock();

        self::assertTrue($normalizer->supportsDenormalization([], PactResponseInterface::class, $format));
    }

    public function test_normalize()
    {
        $response = $this->createMock(PactResponseInterface::class);

        $response
            ->method('getStatusCode')
            ->willReturn($this->rule->hasSample(200));

        $expected = [
            'status_code' => ['_rule' => \get_class($response->getStatusCode()), 'sample' => 200],
        ];

        self::assertEquals($expected, $this->normalizer->normalize($response));
    }

    public function test_denormalize()
    {
        $serializer = SerializerFactory::getInstance();

        $data = [
            'status_code' => 200,
        ];

        /** @var PactResponseInterface $response */
        $response = $serializer->denormalize($data, PactResponseInterface::class);

        self::assertInstanceOf(PactResponseInterface::class, $response);
        self::assertSame(200, $response->getStatusCode()->getSample());
    }

    /**
     * @depends test_denormalize
     */
    public function test_denormalize_html()
    {
        $serializer = SerializerFactory::getInstance();

        $data = [
            'status_code' => 200,
            'headers' => [
                'Content-Type' => [
                    'text/html',
                    'Charset=UTF-8',
                ],
            ],
            'body' => '<html></html>',
        ];

        /** @var PactResponseInterface $pact */
        $pact = $serializer->denormalize($data, PactResponseInterface::class);

        self::assertInstanceOf(StringRule::class, $pact->getHeaders()['Content-Type'][0]);
        self::assertInstanceOf(StringRule::class, $pact->getHeaders()['Content-Type'][1]);
        self::assertInstanceOf(StringRule::class, $pact->getBody());
    }
}
