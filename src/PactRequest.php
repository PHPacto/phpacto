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

use Bigfoot\PHPacto\Matcher\BodyMatcher;
use Bigfoot\PHPacto\Matcher\HeadersMatcher;
use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Request;
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
    private $uri;

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
     * @param Rule             $uri
     * @param Rule[]           $headers
     * @param Rule|Rule[]|null $body
     */
    public function __construct(Rule $method, Rule $uri, array $headers = [], $body = null)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->headers = $headers;
        $this->body = $body;

        $this->assertMatch($this->getSample());
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
    public function getUri(): Rule
    {
        return $this->uri;
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

    public function getSample(): RequestInterface
    {
        $method = strtoupper($this->method->getSample());
        $uri = $this->uri->getSample();

        $headers = [];
        foreach ($this->headers as $key => $rule) {
            $sample = $rule->getSample();

            if (is_array($sample)) {
                foreach ($sample as $i => $val) {
                    $sample[$i] = (string) $val;
                }
            } else {
                $headers[$key] = (string) $sample;
            }
        }

        if ($this->body) {
            if (is_array($this->body)) {
                $body = [];
                foreach ($this->body as $key => $rule) {
                    $body[$key] = $rule instanceof Rule ? $rule->getSample() : $rule;
                }
            } else {
                $body = $this->body->getSample();
            }
        } else {
            $body = '';
        }

        $contentType = @array_change_key_case($headers, CASE_LOWER)['content-type'] ?: '';

        $stream = new Stream('php://memory', 'w');
        $stream->write(BodyEncoder::encode($body, $contentType));

        $response = new Request($uri, $method, $stream, $headers);

        return $response;
    }

    public function assertMatch(RequestInterface $request)
    {
        $mismatches = [];

        try {
            $this->method->assertMatch($request->getMethod());
        } catch (Mismatch $mismatch) {
            $mismatches['Method'] = $mismatch;
        }

        try {
            $this->uri->assertMatch((string) $request->getUri());
        } catch (Mismatch $mismatch) {
            $mismatches['Uri'] = $mismatch;
        }

        if ($this->headers) {
            try {
                $matcher = new HeadersMatcher();
                $matcher->assertMatch($this->headers, $request);
            } catch (Mismatch $mismatch) {
                $mismatches['Headers'] = $mismatch;
            }
        }

        if ($this->body) {
            try {
                $matcher = new BodyMatcher();
                $matcher->assertMatch($this->body, $request);
            } catch (Mismatch $mismatch) {
                $mismatches['Body'] = $mismatch;
            }
        }

        if ($mismatches) {
            throw new MismatchCollection($mismatches, 'Request does not match');
        }
    }
}
