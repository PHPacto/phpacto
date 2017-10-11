<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches\ValueMismatch;
use PHPUnit\Framework\TestCase;

final class RuleMockFactory extends TestCase
{
    final public function empty(): Rule
    {
        return $this->createMock(Rule::class);
    }

    final public function hasValue($value): Rule
    {
        $rule = $this->empty();
        $rule->method('getValue')
            ->willReturn($value);

        return $rule;
    }

    final public function hasValueAndSample($value, $sample): Rule
    {
        $rule = $this->empty();

        $rule->method('getValue')
            ->willReturn($value);

        $rule->method('getSample')
            ->willReturn($sample);

        return $rule;
    }

    final public function hasSample($sample): Rule
    {
        $rule = $this->empty();
        $rule->method('getSample')
            ->willReturn($sample);

        return $rule;
    }

    final public function isMatching(): Rule
    {
        return $this->empty();
    }

    final public function isNotMatching(string $mismatchType = ValueMismatch::class, array $arguments = ['', null, null]): Rule
    {
        $exception = new \ReflectionClass($mismatchType);

        $rule =  $this->empty();
        $rule->method('assertMatch')
            ->willThrowException($exception->newInstanceArgs($arguments));

        return $rule;
    }

    final public function hasSampleNotMatching($sample, string $mismatchType = ValueMismatch::class, array $arguments = ['', null, null]): Rule
    {
        $exception = new \ReflectionClass($mismatchType);

        $rule =  $this->hasSample($sample);
        $rule->method('assertMatch')
            ->willThrowException($exception->newInstanceArgs($arguments));

        return $rule;
    }
}
