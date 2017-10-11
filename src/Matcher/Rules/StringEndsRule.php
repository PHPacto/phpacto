<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class StringEndsRule extends AbstractStringRule
{
    protected function assertSupport($value): void
    {
        parent::assertMatch($value);

        if ($value == '') {
            throw new Mismatches\TypeMismatch('string' , 'empty', 'Cannot compare empty strings');
        }
    }

    public function assertMatch($test): void
    {
        parent::assertMatch($test);

        $function = $this->caseSensitive ? 'strpos' : 'stripos';

        if ($function(strrev($test), strrev($this->value)) !== 0) {
            throw new Mismatches\ValueMismatch('String {{ actual }} should end with {{ expected }}', $this->value, $test);
        }
    }
}
