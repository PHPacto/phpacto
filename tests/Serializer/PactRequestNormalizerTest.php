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

use PHPacto\Factory\SerializerFactory;
use PHPacto\Matcher\Rules\StringRule;
use PHPacto\PactRequestInterface;

class PactRequestNormalizerTest extends SerializerAwareTestCase
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
        /** @var PactRequestNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(PactRequestNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsNormalization', 'getSupportedTypes'])
            ->getMock();

        $pact = $this->createMock(PactRequestInterface::class);

        self::assertTrue($normalizer->supportsNormalization($pact, $format));
    }

    /**
     * @dataProvider normalizationFormatProvider
     */
    public function test_it_supports_denormalization(?string $format)
    {
        /** @var PactRequestNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(PactRequestNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsDenormalization', 'getSupportedTypes'])
            ->getMock();

        self::assertTrue($normalizer->supportsDenormalization([], PactRequestInterface::class, $format));
    }

    public function test_normalize()
    {
        $request = $this->createMock(PactRequestInterface::class);

        $this->rule->map($request->getMethod());
        $this->rule->map($request->getPath());

        $expected = [
            'method' => ['_rule' => \get_class($request->getMethod())],
            'path' => ['_rule' => \get_class($request->getPath())],
        ];

        self::assertEquals($expected, $this->normalizer->normalize($request));
    }

    public function test_denormalize()
    {
        $serializer = SerializerFactory::getInstance();

        $data = [
            'method' => 'get',
            'path' => '/path',
        ];

        /** @var PactRequestInterface $pact */
        $pact = $serializer->denormalize($data, PactRequestInterface::class);

        self::assertInstanceOf(PactRequestInterface::class, $pact);
        self::assertEquals('GET', $pact->getMethod()->getSample());
        self::assertEquals('/path', $pact->getPath()->getSample());
    }

    public function test_denormalize_html()
    {
        $serializer = SerializerFactory::getInstance();

        $data = [
            'method' => 'get',
            'path' => '/',
            'headers' => [
                'Content-Type' => 'text/html; Charset=UTF-8',
            ],
            'body' => '<html></html>',
        ];

        /** @var PactRequestInterface $pact */
        $pact = $serializer->denormalize($data, PactRequestInterface::class);

        self::assertInstanceOf(StringRule::class, $pact->getHeaders()['Content-Type']);
        self::assertInstanceOf(StringRule::class, $pact->getBody());
    }

    /**
     * @depends test_denormalize
     */
    public function test_denormalize_html_exploded_headers()
    {
        $serializer = SerializerFactory::getInstance();

        $data = [
            'method' => 'get',
            'path' => '/',
            'headers' => [
                'Content-Type' => [
                    'text/html',
                    'Charset=UTF-8',
                ],
            ],
            'body' => '<html></html>',
        ];

        /** @var PactRequestInterface $pact */
        $pact = $serializer->denormalize($data, PactRequestInterface::class);

        self::assertInstanceOf(StringRule::class, $pact->getHeaders()['Content-Type'][0]);
        self::assertInstanceOf(StringRule::class, $pact->getHeaders()['Content-Type'][1]);
        self::assertInstanceOf(StringRule::class, $pact->getBody());
    }
}
