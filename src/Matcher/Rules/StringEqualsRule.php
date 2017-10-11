<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class StringEqualsRule extends AbstractStringRule
{
    public function __construct($value, bool $caseSensitive = false)
    {
        parent::__construct($value, $value, $caseSensitive);
    }

    public function assertMatch($test): void
    {
        parent::assertMatch($test);

        if ($this->caseSensitive) {
            if ($this->value != $test) {
                throw new Mismatches\ValueMismatch('String {{ actual }} should be equal to {{ expected }}', $this->value, $test);
            }
        } else {
            if (strtolower($this->value) != strtolower($test)) {
                throw new Mismatches\ValueMismatch('String {{ actual }} should be equal to {{ expected }}', $this->value, $test);
            }
        }
    }
}
