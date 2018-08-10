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
use Bigfoot\PHPacto\Matcher\Rules\EachItemRule;
use Bigfoot\PHPacto\Matcher\Rules\OrRule;
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

        $headers = $this->getSampleRec($this->headers);
        $body = $this->getSampleRec($this->body);

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
            $mismatches['STATUS CODE'] = $mismatch;
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
            throw new MismatchCollection($mismatches, 'Response does not match');
        }
    }

    private function getSampleRec($rule)
    {
        if ($rule instanceof Rule) {
            $sample = $rule->getSample();

            if ($sample === null) {
                if ($rule instanceof EachItemRule) {
                    $sample = [$this->getSampleRec($rule->getRules())];
                } elseif ($rule instanceof OrRule) {
                    $childRules = $rule->getRules();

                    $sample = $childRules[array_rand($childRules)];
                } elseif (method_exists($rule, 'getRule')) {
                    $sample = $rule->getRule();
                }
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
