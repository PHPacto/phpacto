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
use Bigfoot\PHPacto\Matcher\Rules\EachItemRule;
use Bigfoot\PHPacto\Matcher\Rules\OrRule;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Psr\Http\Message\MessageInterface;

abstract class PactMessage implements PactMessageInterface
{
    /**
     * @var Rule[]
     */
    private $headers;

    /**
     * @var Rule|Rule[]|null
     */
    private $body;

    /**
     * @param Rule[]           $headers
     * @param Rule|Rule[]|null $body
     */
    public function __construct(array $headers = [], $body = null)
    {
        $this->headers = $headers;
        $this->body = $body;
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

    protected function assertMatchHeaders(MessageInterface $message)
    {
        if ($this->headers) {
            $matcher = new HeadersMatcher();
            $matcher->assertMatch($this->headers, $message->getHeaders());
        }
    }

    protected function assertMatchBody(MessageInterface $message)
    {
        if ($this->body) {
            $matcher = new BodyMatcher();
            $matcher->assertMatch($this->body, (string) $message->getBody(), $message->getHeaderLine('Content-Type'));
        }
    }

    protected function getSampleHeaders(): array
    {
        return $this->getSampleRec($this->headers);
    }

    protected function getSampleBody()
    {
        return $this->getSampleRec($this->body);
    }

    protected function getSampleRec($rule)
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
    
    protected function getContentType(): ?string
    {
        $val = @array_change_key_case($this->headers, CASE_LOWER)['content-type'];

        if ($val instanceof Rule) {
            return (string) $val->getSample();
        }

        return $val;
    }
}
