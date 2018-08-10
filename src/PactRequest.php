<?php

declare(strict_types=1);

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

use Bigfoot\PHPacto\Matcher\BodyMatcher;
use Bigfoot\PHPacto\Matcher\HeadersMatcher;
use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

class PactRequest implements PactRequestInterface
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
     * @var Rule[]
     */
    private $headers;

    /**
     * @var Rule|Rule[]|null
     */
    private $body;

    /**
     * @param Rule             $method
     * @param Rule             $path
     * @param Rule[]           $headers
     * @param Rule|Rule[]|null $body
     */
    public function __construct(Rule $method, Rule $path, array $headers = [], $body = null)
    {
        $this->method = $method;
        $this->path = $path;
        $this->headers = $headers;
        $this->body = $body;
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

    /**
     * @return Rule[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return Rule|Rule[]|null
     */
    public function getBody()
    {
        return $this->body;
    }

    public function getSample(): ServerRequestInterface
    {
        $method = strtoupper($this->method->getSample());
        $uri = $this->path->getSample();

        $headers = $this->getSampleRec($this->headers);
        $body = $this->getSampleRec($this->body);

        $contentType = @array_change_key_case($headers, CASE_LOWER)['content-type'] ?: '';

        $stream = new Stream('php://memory', 'w');
        $stream->write(BodyEncoder::encode($body, $contentType));

        $response = new ServerRequest([], [], $uri, $method, $stream, $headers, [], [], is_array($body) ? $body : []);

        return $response;
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

        if ($this->headers) {
            try {
                $matcher = new HeadersMatcher();
                $matcher->assertMatch($this->headers, $request);
            } catch (Mismatch $mismatch) {
                $mismatches['HEADERS'] = $mismatch;
            }
        }

        if ($this->body) {
            try {
                $matcher = new BodyMatcher();
                $matcher->assertMatch($this->body, $request);
            } catch (Mismatch $mismatch) {
                $mismatches['BODY'] = $mismatch;
            }
        }

        if ($mismatches) {
            throw new MismatchCollection($mismatches, 'Request does not match');
        }
    }

    private function getSampleRec($rule)
    {
        if ($rule instanceof Rule) {
            $sample = $rule->getSample();

            if ($sample === null && method_exists($rule, 'getRule')) {
                $sample = $rule->getRule();
            }

            return $sample;
        } elseif (is_array($rule)) {
            $result = [];

            foreach ($rule as $key => $value) {
                $result[$key] = $this->getSampleRec($value);
            }

            return $result;
        }

        return $rule;
    }
}
