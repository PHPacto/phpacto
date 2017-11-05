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

namespace Bigfoot\PHPacto;

use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use Bigfoot\PHPacto\Matcher\Rules\RuleMockFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Zend\Diactoros\Response;

class PactResponseTest extends TestCase
{
    /**
     * @var NormalizerInterface|DenormalizerInterface
     */
    protected $normalizer;

    /**
     * @var RuleMockFactory
     */
    private $rule;

    protected function setUp()
    {
        $this->normalizer = SerializerFactory::getInstance();
        $this->rule = new RuleMockFactory();
    }

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

    public function test_that_sample_is_matching_rules_when_instantiating()
    {
        $response = $this->getMockBuilder(PactResponse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response
            ->expects(self::once())
            ->method('getSample')
            ->willReturn($this->createMock(ResponseInterface::class));

        $response
            ->expects(self::once())
            ->method('assertMatch');

        $response->__construct(
            $this->rule->hasSample(200)
        );

        self::assertTrue(true, 'assertMatch is called');
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

    public function test_it_not_throws_mismatch_if_request_match()
    {
        $response = new PactResponse(
            $this->rule->hasSample(200),
            ['x' => $this->rule->hasSample(1)],
            $this->rule->hasSample('body')
        );

        $response->assertMatch(new Response('php://memory', 200, ['x' => 'any']));

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function test_it_throws_mismatch_if_request_not_match()
    {
        $response = new PactResponse(
            $statusCode = $this->rule->hasSample(200),
            ['x' => $header = $this->rule->hasSample(0)],
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
                '@rule' => get_class($mockStatusCode),
                'value' => null,
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
                '@rule' => get_class($mockStatusCode),
                'value' => null,
                'sample' => 201,
            ],
            'headers' => [
                'Y' => [
                    '@rule' => get_class($mockHeaderValue),
                    'value' => null,
                    'sample' => 'X',
                ],
            ],
            'body' => [
                '@rule' => get_class($mockBody),
                'value' => null,
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
                'Y' => 'X',
            ],
            'body' => 'Body',
        ];

        /** @var PactResponseInterface $response */
        $response = $this->normalizer->denormalize($data, PactResponseInterface::class);

        self::assertCount(1, $response->getHeaders());
        self::assertEquals('X', $response->getHeaders()['Y']->getSample());
        self::assertEquals('Body', $response->getBody()->getSample());
    }

    public function test_it_is_denormalizable_body_json()
    {
        $data = [
            'status_code' => 200,
            'headers' => [
                'content-type' => 'application/json',
                'X' => ['Y', 'Z'],
            ],
            'body' => [
                'a' => ['b', 'c'],
            ],
        ];

        /** @var PactResponseInterface $response */
        $response = $this->normalizer->denormalize($data, PactResponseInterface::class);

        self::assertCount(2, $response->getHeaders());
        self::assertEquals('Y', $response->getHeaders()['X'][0]->getSample());
        self::assertEquals('Z', $response->getHeaders()['X'][1]->getSample());

        self::assertCount(1, $response->getBody());
        self::assertCount(2, $response->getBody()['a']);
        self::assertEquals('b', $response->getBody()['a'][0]->getSample());
        self::assertEquals('c', $response->getBody()['a'][1]->getSample());
    }
}
