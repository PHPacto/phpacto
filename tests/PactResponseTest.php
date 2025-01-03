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

use PHPacto\Matcher\Mismatches\Mismatch;
use PHPacto\Matcher\Mismatches\MismatchCollection;
use PHPacto\Serializer\SerializerAwareTestCase;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;

class PactResponseTest extends SerializerAwareTestCase
{
    public function test_has_sample()
    {
        $response = new PactResponse(
            $this->rule->hasSample(201),
            ['X-Custom' => $this->rule->hasSample('X')],
            $this->rule->hasSample('Body')
        );

        $sample = $response->getSample();
        self::assertInstanceOf(ResponseInterface::class, $sample);

        self::assertEquals(201, $sample->getStatusCode());
        self::assertEquals('X', $sample->getHeaderLine('X-Custom'));
        self::assertEquals('Body', (string) $sample->getBody());
    }

    public function test_has_sample_with_body_url_encoded()
    {
        $response = new PactResponse(
            $this->rule->hasSample(200),
            ['Content-Type' => $this->rule->hasSample('application/x-www-form-urlencoded')],
            $this->rule->hasSample(['x' => ['content']])
        );

        $sample = $response->getSample();
        self::assertEquals('x%5B0%5D=content', (string) $sample->getBody());
    }

    /**
     * @depends test_has_sample
     */
    public function test_has_sample_with_body_json_encoded()
    {
        $response = new PactResponse(
            $this->rule->hasSample(200),
            ['Content-Type' => $this->rule->hasSample('application/json')],
            $this->rule->hasSample(['x' => ['content']])
        );

        $sample = $response->getSample();
        self::assertJsonStringEqualsJsonString('{"x":["content"]}', (string) $sample->getBody());
    }

    public function test_it_match_if_request_match()
    {
        $request = new PactResponse(
            $this->rule->hasSample(200),
            ['X' => $this->rule->hasSample(1)],
            $this->rule->hasSample('body')
        );

        $request->assertMatch(new Response('php://memory', 200, ['x' => 1]));

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function test_it_not_throws_mismatch_if_request_match()
    {
        $response = new PactResponse(
            $this->rule->hasSample(200),
            ['X' => $this->rule->hasSample(1)],
            $this->rule->hasSample('body')
        );

        $response->assertMatch(new Response('php://memory', 200, ['x' => 'any']));

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function test_it_throws_mismatch_if_request_not_match()
    {
        $response = new PactResponse(
            $statusCode = $this->rule->hasSample(200),
            ['0' => $header = $this->rule->hasSample(0)],
            $body = $this->rule->hasSample('')
        );

        $statusCode
            ->expects(self::once())
            ->method('assertMatch')
            ->willThrowException($this->createMock(Mismatch::class));

        $header
            ->expects(self::once())
            ->method('assertMatch')
            ->willThrowException($this->createMock(Mismatch::class));

        $body
            ->expects(self::once())
            ->method('assertMatch')
            ->willThrowException($this->createMock(Mismatch::class));

        try {
            $response->assertMatch(new Response('php://memory', 200, ['x' => 'any']));
        } catch (MismatchCollection $mismatchCollection) {
            self::assertEquals(3, $mismatchCollection->countAll());

            return;
        }

        self::fail('This test should end in the catch');
    }

    public function test_it_is_normalizable_minimal()
    {
        $response = new PactResponse(
            $mockStatusCode = $this->rule->hasSample(200)
        );

        $expected = [
            'status_code' => [
                '_rule' => \get_class($mockStatusCode),
                'sample' => 200,
            ],
        ];

        self::assertEquals($expected, $this->normalizer->normalize($response));
    }

    /**
     * @depends test_it_is_normalizable_minimal
     */
    public function test_it_is_normalizable_full()
    {
        $response = new PactResponse(
            $mockStatusCode = $this->rule->hasSample(201),
            ['Y' => $mockHeaderValue = $this->rule->hasSample('X')],
            $mockBody = $this->rule->hasSample('Body')
        );

        $expected = [
            'status_code' => [
                '_rule' => \get_class($mockStatusCode),
                'sample' => 201,
            ],
            'headers' => [
                'Y' => [
                    '_rule' => \get_class($mockHeaderValue),
                    'sample' => 'X',
                ],
            ],
            'body' => [
                '_rule' => \get_class($mockBody),
                'sample' => 'Body',
            ],
        ];

        self::assertEquals($expected, $this->normalizer->normalize($response));
    }

    public function test_it_is_denormalizable_minimal()
    {
        $data = [
            'status_code' => 200,
        ];

        /** @var PactResponseInterface $response */
        $response = $this->normalizer->denormalize($data, PactResponseInterface::class);

        self::assertInstanceOf(PactResponseInterface::class, $response);
        self::assertEquals(200, $response->getStatusCode()->getSample());
    }

    public function test_it_is_denormalizable_full()
    {
        $data = [
            'status_code' => 200,
            'headers' => [
                'x-key' => 'val',       // "x-key" will be normalized to CamelCase "X-Key"
                'y' => ['Y', 'Z'],
            ],
            'body' => 'Body',
        ];

        /** @var PactResponseInterface $response */
        $response = $this->normalizer->denormalize($data, PactResponseInterface::class);

        self::assertCount(2, $response->getHeaders()['Y']);
        self::assertEquals('val', $response->getHeaders()['X-Key']->getSample());
        self::assertEquals('Y', $response->getHeaders()['Y'][0]->getSample());
        self::assertEquals('Z', $response->getHeaders()['Y'][1]->getSample());
        self::assertEquals('Body', $response->getBody()->getSample());
    }
}
