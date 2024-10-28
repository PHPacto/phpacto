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

namespace PHPacto\Matcher\Rules;

use PHPacto\Matcher\Mismatches\ValueMismatch;
use PHPacto\Serializer\RuleMap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RuleMockFactory extends TestCase
{
    /**
     * @var RuleMap
     */
    private $ruleMap;

    public function __construct(RuleMap $ruleMap = null)
    {
        parent::__construct();

        $this->ruleMap = $ruleMap;
    }

    public function map(MockObject $mock): void
    {
        $this->ruleMap->addRule(\get_class($mock), \get_class($mock));
    }

    public function empty(string $type = Rule::class): Rule
    {
        $mock = $this->createMock($type);

        if ($this->ruleMap) {
            $this->map($mock);
        }

        return $mock;
    }

    public function hasSample($sample, string $type = Rule::class): Rule
    {
        $rule = $this->empty($type);
        $rule->method('getSample')
            ->willReturn($sample);
        $rule->method('hasSample')
            ->willReturn(true);

        return $rule;
    }

    public function matching(): Rule
    {
        return $this->empty();
    }

    public function notMatching(string $mismatchType = ValueMismatch::class, array $arguments = ['A mismatch is thrown', null, null]): Rule
    {
        $exception = new \ReflectionClass($mismatchType);

        $rule = $this->empty();
        $rule->method('assertMatch')
            ->willThrowException($exception->newInstanceArgs($arguments));

        return $rule;
    }

    public function hasSampleNotMatching($sample, string $mismatchType = ValueMismatch::class, array $arguments = ['', null, null]): Rule
    {
        $exception = new \ReflectionClass($mismatchType);

        $rule = $this->hasSample($sample);
        $rule->method('assertMatch')
            ->willThrowException($exception->newInstanceArgs($arguments));

        return $rule;
    }
}
