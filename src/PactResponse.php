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

use Bigfoot\PHPacto\Matcher\BodyMatcher;
use Bigfoot\PHPacto\Matcher\HeadersMatcher;
use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

class PactResponse implements PactResponseInterface
{
    /**
     * @var Rule
     */
    private $statusCode;

    /**
     * @var Rule[]
     */
    private $headers;

    /**
     * @var Rule|Rule[]|null
     */
    private $body;

    /**
     * @param Rule      $statusCode
     * @param Rule[]    $headers
     * @param Rule|null $body
     */
    public function __construct(Rule $statusCode, array $headers = [], $body = null)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;

        $this->assertMatch($this->getSample());
    }

    /**
     * @return Rule
     */
    public function getStatusCode(): Rule
    {
        return $this->statusCode;
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

    public function getSample(): ResponseInterface
    {
        $statusCode = $this->statusCode->getSample();

//        $headers = [];
//        foreach ($this->headers as $key => $rule) {
//            $sample = $rule->getSample();
//
//            if (is_array($sample)) {
//                foreach ($sample as $i => $val) {
//                    $sample[$i] = (string) $val;
//                }
//            } else {
//                $headers[$key] = (string) $sample;
//            }
//        }

        if ($this->headers) {
            $headers = $this->getSampleRec($this->headers);
        } else {
            $headers = [];
        }

        if ($this->body) {
            $body = $this->getSampleRec($this->body);
        } else {
            $body = '';
        }

        $contentType = @array_change_key_case($headers, CASE_LOWER)['content-type'] ?: '';

        $stream = new Stream('php://memory', 'w');
        $stream->write(BodyEncoder::encode($body, $contentType));

        $response = new Response($stream, $statusCode, $headers);

        return $response;
    }

    public function assertMatch(ResponseInterface $request)
    {
        $mismatches = [];

        try {
            $this->statusCode->assertMatch($request->getStatusCode());
        } catch (Mismatch $mismatch) {
            $mismatches['Status code'] = $mismatch;
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
            throw new MismatchCollection($mismatches, 'Response does not match');
        }
    }

    private function getSampleRec($data)
    {
        if ($data instanceof Rule) {
            return $data->getSample();
        } elseif (is_array($data)) {
            $result = [];

            foreach ($data as $key => $value) {
                $result[$key] = $this->getSampleRec($value);
            }

            return $result;
        }

        return $data;
    }
}
