<?php

namespace Bigfoot\PHPacto\Matcher\Mismatches;

use PHPUnit\Framework\TestCase;

class TypeMismatchTest extends TestCase
{
    public function test_has_message_string()
    {
        $miss = new TypeMismatch('expected', 'actual', 'Type {{ expected }} was expected, but got {{ actual }} instead');

        self::assertEquals('Type `expected` was expected, but got `actual` instead', $miss->getMessage());
    }

    public function test_is_has_actual_and_expected_values()
    {
        $miss = new TypeMismatch('expected', 'actual');

        self::assertEquals('expected', $miss->getExpected());
        self::assertEquals('actual', $miss->getActual());
    }
}
