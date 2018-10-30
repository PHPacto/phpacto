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

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches\ValueMismatch;
use Bigfoot\PHPacto\Serializer\RuleMap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

// For PhpUnit:6 back-compatibility
if (!\interface_exists(MockObject::class)) {
    \class_alias('PHPUnit_Framework_MockObject_MockObject', MockObject::class);
}

final class RuleMockFactory extends TestCase
{
    /**
     * @var RuleMap
     */
    private $ruleMap;

    public function __construct(RuleMap $ruleMap = null)
    {
        $this->ruleMap = $ruleMap;
    }

    public function map(MockObject $mock): void
    {/*
        $refl = new \ReflectionClass($mock);
        var_dump($refl->getName());
        var_dump($refl->getInterfaces());
        var_dump($refl->getParentClass());*/
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

    public function hasSample($sample): Rule
    {
        $rule = $this->empty();
        $rule->method('getSample')
            ->willReturn($sample);

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
