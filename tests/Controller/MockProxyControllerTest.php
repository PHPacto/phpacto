<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2018  Damian Długosz
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

namespace Bigfoot\PHPacto\Controller;

use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Logger\Logger;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

class MockProxyControllerTest extends TestCase
{
    /**
     * @var vfsStream
     */
    protected $fs;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var MockProxyController
     */
    protected $controller;

    /**
     * @var string
     */
    protected $proxyTo;

    public function setUp()
    {
        $guzzleVersion = \GuzzleHttp\ClientInterface::VERSION;

        if (\version_compare($guzzleVersion, '6', '<') || \version_compare($guzzleVersion, '7', '>=')) {
            self::markTestSkipped('MockProxyController works with Guzzle 6 or newer');
        }

        $this->client = $this->createMock(ClientInterface::class);
        $this->logger = $this->createMock(Logger::class);
        $this->serializer = SerializerFactory::getInstance();
        $this->proxyTo = 'http://proxied-host:8888/proxied-dir';

        // Define my virtual file system
        $directory = [
        ];

        // Setup and cache the virtual file system
        $this->fs = vfsStream::setup('root', 444, $directory);

        $this->controller = new MockProxyController($this->client, $this->logger, $this->proxyTo, $this->fs->url());
    }

    /**
     * @cover MockProxyController
     */
    public function test_it_proxies_request_and_records_contract()
    {
        // A client wiil make a request like this
        $stream = new Stream('php://memory', 'rw');
        $request = new Request('/my-path-test', 'method', $stream, ['X' => 'REQUEST HEADERS']);
        $stream->write('Request Body');

        // The proxied server will respond with
        $stream = new Stream('php://memory', 'rw');
        $response = new Response($stream, 418, ['Y' => 'RESPONSE HEADERS']);
        $stream->write('Response Body');

        $this->client->expects(self::once())
            ->method('request')
        // Assertions on Request to being send to proxied server
            ->with('method', 'http://proxied-host:8888/proxied-dir/my-path-test', ['headers' => ['X' => ['REQUEST HEADERS']], 'body' => 'Request Body', 'allow_redirects' => false])
            ->willReturn($response);

        $response = $this->controller->action($request);

        // Assertions on Response coming from proxied server
        self::assertEquals(418, $response->getStatusCode());
        self::assertEquals('RESPONSE HEADERS', $response->getHeaderLine('Y'));
        self::assertEquals('Response Body', (string) $response->getBody());

        // Assert that a cantract is generated
        self::assertTrue($this->fs->hasChildren(), 'Any contract has been recorded');

        // Assertions on recorded contract
        $contract = $this->fs->getChildren()[0]->getContent();

        // Contract Request
        self::assertStringContains('method: METHOD', $contract);
        self::assertStringContains('path: /my-path-test', $contract);
        self::assertStringContains('X: \'REQUEST HEADERS\'', $contract);
        self::assertStringContains('body: \'Request Body\'', $contract);

        // Contract Response
        self::assertStringContains('status_code: 418', $contract);
        self::assertStringContains('\'Y\': \'RESPONSE HEADERS\'', $contract);
        self::assertStringContains('body: \'Response Body\'', $contract);
    }

    /**
     * @cover MockProxyController
     */
    public function test_it_proxies_request_and_records_contract_when_server_respond_with_error()
    {
        // A client wiil make a request like this
        $stream = new Stream('php://memory', 'rw');
        $request = new Request('/my-path-test', 'method', $stream, ['X' => 'REQUEST HEADERS']);
        $stream->write('Request Body');

        // The proxied server will respond with
        $stream = new Stream('php://memory', 'rw');
        $response = new Response($stream, 418, ['Y' => 'RESPONSE HEADERS']);
        $stream->write('Response Body');

        $this->client->expects(self::once())
            ->method('request')
        // Assertions on Request to being send to proxied server
            ->with('method', 'http://proxied-host:8888/proxied-dir/my-path-test', ['headers' => ['X' => ['REQUEST HEADERS']], 'body' => 'Request Body', 'allow_redirects' => false])
            ->willThrowException(new BadResponseException('Server respond with a BAD status code', $request, $response));

        $response = $this->controller->action($request);

        // Assertions on Response coming from proxied server
        self::assertEquals(418, $response->getStatusCode());
        self::assertEquals('RESPONSE HEADERS', $response->getHeaderLine('Y'));
        self::assertEquals('Response Body', (string) $response->getBody());

        // Assert that a cantract is generated
        self::assertTrue($this->fs->hasChildren(), 'Any contract has been recorded');

        // Assertions on recorded contract
        $contract = $this->fs->getChildren()[0]->getContent();

        // Contract Request
        self::assertStringContains('method: METHOD', $contract);
        self::assertStringContains('path: /my-path-test', $contract);
        self::assertStringContains('X: \'REQUEST HEADERS\'', $contract);
        self::assertStringContains('body: \'Request Body\'', $contract);

        // Contract Response
        self::assertStringContains('status_code: 418', $contract);
        self::assertStringContains('\'Y\': \'RESPONSE HEADERS\'', $contract);
        self::assertStringContains('body: \'Response Body\'', $contract);
    }

    private static function assertStringContains(string $needle, string $haystack, string $message = '')
    {
        self::assertRegexp('/' . \preg_quote($needle, '/') . '/', $haystack, $message);
    }
}
