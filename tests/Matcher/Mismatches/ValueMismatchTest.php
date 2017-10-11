<?php

namespace Bigfoot\PHPacto\Matcher\Mismatches;

use PHPUnit\Framework\TestCase;

class ValueMismatchTest extends TestCase
{
    public function test_has_message_string()
    {
        $miss = new ValueMismatch('Was expected {{ expected }}, instead got {{ actual }}', 'expected', 'actual');

        self::assertEquals('Was expected `expected`, instead got `actual`', $miss->getMessage());
    }

    public function test_is_has_actual_and_expected_values()
    {
        $miss = new ValueMismatch('message', 'expected', 'actual');

        self::assertEquals('expected', $miss->getExpected());
        self::assertEquals('actual', $miss->getActual());
    }
}
