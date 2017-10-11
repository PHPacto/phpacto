<?php

namespace Bigfoot\PHPacto\Matcher\Mismatches;

use PHPUnit\Framework\TestCase;

class KeyNotFoundMismatchTest extends TestCase
{
    public function test_has_message_string()
    {
        $miss = new KeyNotFoundMismatch('key');

        self::assertEquals('Key `key` was not found', $miss->getMessage());
    }

    public function test_is_has_expected_key_name()
    {
        $miss = new KeyNotFoundMismatch('key');

        self::assertEquals('key', $miss->getExpected());
    }
}
