<?php

/*
 * This file is part of PHPacto
 *
 * Copyright (c) 2017  Damian Długosz <bigfootdd@gmail.com>
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
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Zend\Diactoros\Request;

class PactRequestTest extends TestCase
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
        $request = new PactRequest(
            $this->rule->hasSample('get'),
            $this->rule->hasSample('/'),
            ['X-Custom' => $this->rule->hasSample('X')],
            $this->rule->hasSample('Body')
        );

        $sample = $request->getSample();
        self::assertInstanceOf(RequestInterface::class, $sample);

        self::assertEquals('GET', $sample->getMethod());
        self::assertEquals('/', $sample->getUri());
        self::assertEquals('X', $sample->getHeaderLine('X-Custom'));
        self::assertEquals('Body', (string) $sample->getBody());
    }

    public function test_that_sample_is_matching_rules_when_instantiating()
    {
        $request = $this->getMockBuilder(PactRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request
            ->expects(self::once())
            ->method('getSample')
            ->willReturn($this->createMock(RequestInterface::class));

        $request
            ->expects(self::once())
            ->method('assertMatch');

        $request->__construct(
            $this->rule->hasSample('get'),
            $this->rule->hasSample('/')
        );

        self::assertTrue(true, 'assertMatch is called');
    }

    public function test_has_sample_with_body_url_encoded()
    {
        $request = new PactRequest(
            $this->rule->hasSample('get'),
            $this->rule->hasSample('/'),
            ['Content-Type' => $this->rule->hasSample('application/x-www-form-urlencoded')],
            $this->rule->hasSample(['x' => ['content']], ['x' => ['content']])
        );

        $sample = $request->getSample();
        self::assertEquals('x%5B0%5D=content', (string) $sample->getBody());
    }

    /**
     * @depends test_has_sample
     */
    public function test_has_sample_with_body_json_encoded()
    {
        $request = new PactRequest(
            $this->rule->hasSample('get'),
            $this->rule->hasSample('/'),
            ['Content-Type' => $this->rule->hasSample('application/json')],
            $this->rule->hasSample(['x' => ['content']])
        );

        $sample = $request->getSample();
        self::assertJsonStringEqualsJsonString('{"x":["content"]}', (string) $sample->getBody());
    }

    public function test_it_match_if_request_match()
    {
        $request = new PactRequest(
            $this->rule->hasSample('get'),
            $this->rule->hasSample('/'),
            ['x' => $this->rule->hasSample('y')],
            $this->rule->hasSample('body')
        );

        $request->assertMatch(new Request('/', 'Get', 'php://memory', ['x' => 'any']));

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function test_it_throws_mismatch_if_request_not_match()
    {
        $request = new PactRequest(
            $method = $this->rule->hasSample('get'),
            $uri = $this->rule->hasSample('/'),
            ['x' => $header = $this->rule->hasSample(0)],
            $body = $this->rule->hasSample('')
        );

        $method
            ->expects(self::once())
            ->method('assertMatch')
            ->willThrowException($this->createMock(Mismatch::class));

        $uri
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
            $request->assertMatch(new Request('/uri', 'Post'), 'php://memory', ['x' => 'any']);
        } catch (MismatchCollection $mismatch) {
            self::assertEquals(4, $mismatch->countAll());

            return;
        }

        self::fail('This test should end in the catch');
    }

    public function test_it_is_normalizable_minimal()
    {
        $request = new PactRequest(
            $mockMethod = $this->rule->hasSample('get'),
            $mockUri = $this->rule->hasSample('/')
        );

        $expected = [
            'method' => [
                '@rule' => get_class($mockMethod),
                'sample' => 'get',
            ],
            'uri' => [
                '@rule' => get_class($mockUri),
                'sample' => '/',
            ],
        ];

        self::assertEquals($expected, $this->normalizer->normalize($request));
    }

    /**
     * @depends test_it_is_normalizable_minimal
     */
    public function test_it_is_normalizable_full()
    {
        $request = new PactRequest(
            $mockMethod = $this->rule->hasSample('put'),
            $mockUri = $this->rule->hasSample('/path'),
            ['Y' => $mockHeaderValue = $this->rule->hasSample('X')],
            $mockBody = $this->rule->hasSample('Body')
        );

        $expected = [
            'method' => [
                '@rule' => get_class($mockMethod),
                'sample' => 'put',
            ],
            'uri' => [
                '@rule' => get_class($mockUri),
                'sample' => '/path',
            ],
            'headers' => [
                'Y' => [
                    '@rule' => get_class($mockHeaderValue),
                    'sample' => 'X',
                ],
            ],
            'body' => [
                '@rule' => get_class($mockBody),
                'sample' => 'Body',
            ],
        ];

        self::assertEquals($expected, $this->normalizer->normalize($request));
    }

    public function test_it_is_denormalizable_minimal()
    {
        $data = [
            'method' => 'GET',
            'uri' => '/',
        ];

        /** @var PactRequestInterface $request */
        $request = $this->normalizer->denormalize($data, PactRequestInterface::class);

        self::assertInstanceOf(PactRequestInterface::class, $request);
        self::assertEquals('GET', $request->getMethod()->getSample());
        self::assertEquals('/', $request->getUri()->getSample());
    }

    public function test_it_is_denormalizable_full()
    {
        $data = [
            'method' => 'POST',
            'uri' => '/path?query',
            'headers' => [
                'Y' => 'X',
            ],
            'body' => 'Body',
        ];

        /** @var PactRequestInterface $request */
        $request = $this->normalizer->denormalize($data, PactRequestInterface::class);

        self::assertEquals('POST', $request->getMethod()->getSample());
        self::assertEquals('/path?query', $request->getUri()->getSample());
        self::assertEquals('X', $request->getHeaders()['Y']->getSample());
        self::assertEquals('Body', $request->getBody()->getSample());
    }
}
