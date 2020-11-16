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

namespace PHPacto;

use PHPacto\Matcher\Mismatches\Mismatch;
use PHPacto\Matcher\Mismatches\MismatchCollection;
use PHPacto\Matcher\Rules\StringEqualsRule;
use PHPacto\Matcher\Rules\StringRule;
use PHPacto\Matcher\Rules\UrlRule;
use PHPacto\Serializer\SerializerAwareTestCase;
use Laminas\Diactoros\Request;
use Psr\Http\Message\RequestInterface;

class PactRequestTest extends SerializerAwareTestCase
{
    public function test_has_sample()
    {
        $request = new PactRequest(
            $this->rule->hasSample('get', StringRule::class),
            $this->rule->hasSample('/', StringRule::class),
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

    public function test_has_sample_with_body_url_encoded()
    {
        $request = new PactRequest(
            $this->rule->hasSample('get', StringRule::class),
            $this->rule->hasSample('/', StringRule::class),
            ['Content-Type' => $this->rule->hasSample('application/x-www-form-urlencoded')],
            $this->rule->hasSample(['x' => ['content']])
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
            $this->rule->hasSample('get', StringRule::class),
            $this->rule->hasSample('/', StringRule::class),
            ['Content-Type' => $this->rule->hasSample('application/json')],
            $this->rule->hasSample(['x' => ['content']])
        );

        $sample = $request->getSample();
        self::assertJsonStringEqualsJsonString('{"x":["content"]}', (string) $sample->getBody());
    }

    public function test_it_match_if_request_match()
    {
        $request = new PactRequest(
            $this->rule->hasSample('get', StringRule::class),
            $this->rule->hasSample('/', StringRule::class),
            ['X' => $this->rule->hasSample('y')],
            $this->rule->hasSample('body')
        );

        $request->assertMatch(new Request('/', 'Get', 'php://memory', ['x' => 'any']));

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function test_it_match_with_request_query_url_encoded()
    {
        $request = new PactRequest(
            $this->rule->hasSample('get', StringRule::class),
            new StringEqualsRule('/url-encoded?param[key]=value'),
            [],
            $this->rule->hasSample('body')
        );

        $request->assertMatch(new Request('/url-encoded?param%5Bkey%5D=value', 'Get', 'php://memory'));

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function test_it_throws_mismatch_if_request_not_match()
    {
        $request = new PactRequest(
            $method = $this->rule->hasSample('get', StringRule::class),
            $path = $this->rule->hasSample('/', StringRule::class),
            ['X' => $header = $this->rule->hasSample(0)],
            $body = $this->rule->hasSample('')
        );

        $method
            ->expects(self::once())
            ->method('assertMatch')
            ->willThrowException($this->createMock(Mismatch::class));

        $path
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
            $request->assertMatch(new Request('/path', 'Post'), 'php://memory', ['x' => 'any']);
        } catch (MismatchCollection $mismatch) {
            self::assertEquals(4, $mismatch->countAll());

            return;
        }

        self::fail('This test should end in the catch');
    }

    public function test_it_is_normalizable_minimal()
    {
        $request = new PactRequest(
            $mockMethod = $this->rule->hasSample('get', StringRule::class),
            $mockPath = $this->rule->hasSample('/', StringRule::class)
        );

        $expected = [
            'method' => [
                '_rule' => \get_class($mockMethod),
                'sample' => 'get',
            ],
            'path' => [
                '_rule' => \get_class($mockPath),
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
            $mockMethod = $this->rule->hasSample('put', StringRule::class),
            $mockPath = $this->rule->hasSample('/path', UrlRule::class),
            ['Y' => $mockHeaderValue = $this->rule->hasSample('X')],
            $mockBody = $this->rule->hasSample('Body')
        );

        $expected = [
            'method' => [
                '_rule' => \get_class($mockMethod),
                'sample' => 'put',
            ],
            'path' => [
                '_rule' => \get_class($mockPath),
                'location' => '',
                'sample' => '/path',
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

        self::assertEquals($expected, $this->normalizer->normalize($request));
    }

    public function test_it_is_denormalizable_minimal()
    {
        $data = [
            'method' => 'GET',
            'path' => '/',
        ];

        /** @var PactRequestInterface $request */
        $request = $this->normalizer->denormalize($data, PactRequestInterface::class);

        self::assertInstanceOf(PactRequestInterface::class, $request);
        self::assertEquals('GET', $request->getMethod()->getSample());
        self::assertEquals('/', $request->getPath()->getSample());
    }

    public function test_it_is_denormalizable_full()
    {
        $data = [
            'method' => 'POST',
            'path' => [
                '_rule' => 'url',
                'location' => '/path',
                'query' => [
                    'qp1' => 'A',
                ],
                'sample' => '/path?qp1=A',
            ],
            'headers' => [
                'x-key' => 'val',       // "x-key" will be normalized to CamelCase "X-Key"
            ],
            'body' => 'Body',
        ];

        /** @var PactRequestInterface $request */
        $request = $this->normalizer->denormalize($data, PactRequestInterface::class);

        self::assertEquals('POST', $request->getMethod()->getSample());
        self::assertEquals('/path?qp1=A', $request->getPath()->getSample());
        self::assertEquals('/path', $request->getPath()->getLocation());
        self::assertEquals('A', $request->getPath()->getQuery()->getProperties()['qp1']->getSample());
        self::assertEquals('val', $request->getHeaders()['X-Key']->getSample());
        self::assertEquals('Body', $request->getBody()->getSample());
    }
}
