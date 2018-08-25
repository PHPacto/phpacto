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

namespace Bigfoot\PHPacto;

use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

class PactRequest extends PactMessage implements PactRequestInterface
{
    /**
     * @var Rule
     */
    private $method;

    /**
     * @var Rule
     */
    private $path;

    /**
     * @param Rule             $method
     * @param Rule             $path
     * @param Rule[]           $headers
     * @param Rule|Rule[]|null $body
     */
    public function __construct(Rule $method, Rule $path, array $headers = [], $body = null)
    {
        parent::__construct($headers, $body);

        $this->method = $method;
        $this->path = $path;
    }

    /**
     * @return Rule
     */
    public function getMethod(): Rule
    {
        return $this->method;
    }

    /**
     * @return Rule
     */
    public function getPath(): Rule
    {
        return $this->path;
    }

    public function assertMatch(RequestInterface $request)
    {
        $mismatches = [];

        try {
            $this->method->assertMatch($request->getMethod());
        } catch (Mismatch $mismatch) {
            $mismatches['METHOD'] = $mismatch;
        }

        try {
            $uri = urldecode((string) $request->getUri());
            $this->path->assertMatch($uri);
        } catch (Mismatch $mismatch) {
            $mismatches['URI'] = $mismatch;
        }

        try {
            $this->assertMatchHeaders($request);
        } catch (Mismatch $mismatch) {
            $mismatches['HEADERS'] = $mismatch;
        }

        try {
            $this->assertMatchBody($request);
        } catch (Mismatch $mismatch) {
            $mismatches['BODY'] = $mismatch;
        }

        if ($mismatches) {
            throw new MismatchCollection($mismatches, 'Request does not match');
        }
    }

    public function getSample(): ServerRequestInterface
    {
        $method = strtoupper($this->method->getSample());
        $uri = $this->path->getSample();

        $headers = $this->getSampleHeaders();
        $body = $this->getSampleBody();

        $contentType = $this->getContentType();

        $stream = new Stream('php://memory', 'w');
        $stream->write(BodyEncoder::encode($body, $contentType));

        $response = new ServerRequest([], [], $uri, $method, $stream, $headers, [], [], is_array($body) ? $body : []);

        return $response;
    }
}
