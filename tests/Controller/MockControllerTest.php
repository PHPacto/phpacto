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

use Bigfoot\PHPacto\Logger\Logger;
use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\Pact;
use Bigfoot\PHPacto\PactRequestInterface;
use Bigfoot\PHPacto\PactResponseInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

class MockControllerTest extends TestCase
{
    /**
     * @var Logger
     */
    protected $logger;

    public function setUp()
    {
        $this->logger = $this->createMock(Logger::class);
    }

    public function test_it_founds_a_pact_matching_request_and_returns_response()
    {
        $notMatchingPact = $this->createMock(Pact::class);
        $matchingPact = $this->createMock(Pact::class);

        $notMatchingRequest = $this->createMock(PactRequestInterface::class);
        $notMatchingRequest->expects(self::once())
            ->method('assertMatch')
            ->willThrowException($this->createPartialMock(Mismatch::class, []));

        $notMatchingPact->expects(self::once())
            ->method('getRequest')
            ->willReturn($notMatchingRequest);

        $matchingRequest = $this->createMock(PactRequestInterface::class);
        $matchingRequest->expects(self::once())
            ->method('assertMatch')
            ->willReturn(true);

        $matchingResponse = $this->createMock(PactResponseInterface::class);
        $matchingResponse->expects(self::once())
            ->method('getSample')
            ->willReturn($matchingResponsePsr7 = $this->createMock(ResponseInterface::class));

        $matchingPact->expects(self::once())
            ->method('getRequest')
            ->willReturn($matchingRequest);

        $matchingPact->expects(self::once())
            ->method('getResponse')
            ->willReturn($matchingResponse);

        $controller = new MockController($this->logger, [$notMatchingPact, $matchingPact]);

        $response = $controller->action(new Request());

        self::assertSame($matchingResponsePsr7, $response);
    }

    public function test_it_throws_exception_if_any_pact_is_matching()
    {
        $controller = new MockController($this->logger, []);

        self::expectExceptionMessage('No contract found matching your request');

        $controller->action(new Request());
    }

    public function test_support_cqrs()
    {
        $matchingPact = $this->createMock(Pact::class);

        $matchingResponse = $this->createMock(PactResponseInterface::class);
        $matchingResponse->expects(self::once())
            ->method('getSample')
            ->willReturn(new Response());

        $matchingPact->expects(self::once())
            ->method('getResponse')
            ->willReturn($matchingResponse);

        $controller = new MockController($this->logger, [$matchingPact], '*.origin.it');

        $response = $controller->action(new Request());

        self::assertEquals('*.origin.it', $response->getHeaderLine('Access-Control-Allow-Origin'));
        self::assertEquals('*', $response->getHeaderLine('Access-Control-Allow-Headers'));
        self::assertEquals('True', $response->getHeaderLine('Access-Control-Allow-Credentials'));
    }
}
