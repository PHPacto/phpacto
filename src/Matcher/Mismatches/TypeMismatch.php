<?php

namespace Bigfoot\PHPacto\Matcher\Mismatches;

class TypeMismatch extends ValueMismatch
{
    /**
     * @param string|string[] $expected
     * @param string $actual
     * @param string|null $message
     */
    public function __construct($expected, string $actual, string $message = null)
    {
        parent::__construct($message ?: 'A/An {{ expected }} was expected, but got {{ actual }} instead', $expected, $actual);
    }
}
