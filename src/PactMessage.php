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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 */

namespace PHPacto;

use PHPacto\Encoder\BodyEncoder;
use PHPacto\Encoder\HeadersEncoder;
use PHPacto\Matcher\BodyMatcher;
use PHPacto\Matcher\HeadersMatcher;
use PHPacto\Matcher\Rules\EachItemRule;
use PHPacto\Matcher\Rules\ObjectRule;
use PHPacto\Matcher\Rules\OrRule;
use PHPacto\Matcher\Rules\Rule;
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

    public function getSampleHeaders(): array
    {
        return HeadersEncoder::encode($this->getSampleRecursive($this->headers));
    }

    public function getSampleBody()
    {
        return $this->getSampleRecursive($this->body);
    }

    protected function assertMatchHeaders(MessageInterface $message)
    {
        if ($this->headers) {
            $headers = HeadersEncoder::decode($message->getHeaders());

            $this->getHeadersMatcher()->assertMatch($this->headers, $headers);
        }
    }

    protected function assertMatchBody(MessageInterface $message)
    {
        if ($this->body) {
            $messageBody = BodyEncoder::decode((string) $message->getBody(), $message->getHeaderLine('Content-Type'));

            $this->getBodyMatcher()->assertMatch($this->body, $messageBody);
        }
    }

    protected function getSampleRecursive($rule)
    {
        if ($rule instanceof Rule) {
            $sample = $rule->getSample();

            if (null === $sample) {
                if ($rule instanceof ObjectRule) {
                    $sample = $this->getSampleRecursive($rule->getProperties());
                } elseif ($rule instanceof EachItemRule) {
                    $sample = [
                        $this->getSampleRecursive($rule->getRules()),
                    ];
                } elseif ($rule instanceof OrRule) {
                    $childRules = $rule->getRules();

                    $sample = $childRules[array_rand($childRules)];
                } elseif (method_exists($rule, 'getRule')) {
                    $sample = $rule->getRule();
                }
            }

            return $sample;
        } elseif (\is_array($rule)) {
            $result = [];

            foreach ($rule as $key => $value) {
                $result[$key] = $this->getSampleRecursive($value);
            }

            return $result;
        }

        return $rule;
    }

    protected function getContentType(): ?string
    {
        $val = @array_change_key_case($this->headers, CASE_LOWER)['content-type'];

        if (\is_array($val) && \count($val) >= 1) {
            $val = $val[0];
        }

        if ($val instanceof Rule) {
            return (string) $val->getSample();
        }

        return $val;
    }

    protected function getHeadersMatcher(): HeadersMatcher
    {
        return new HeadersMatcher();
    }

    protected function getBodyMatcher(): BodyMatcher
    {
        return new BodyMatcher();
    }
}
