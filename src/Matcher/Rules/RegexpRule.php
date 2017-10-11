<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class RegexpRule extends AbstractRule
{
    public function __construct($value, $sample = null)
    {
        $this->assertSupport($value);

        parent::__construct($value, $sample);

        if (null !== $sample) {
            $this->assertMatch($sample);
        }
    }

    protected function assertSupport($value): void
    {
        if (!is_string($value)) {
            throw new Mismatches\TypeMismatch('string' , gettype($value));
        } elseif(false === @preg_match('/'.$value.'/', null)) {
            throw new Mismatches\TypeMismatch('regex pattern' , $value, 'Your expression is not valid, check syntax for your pattern {{ actual }}');
        }
    }

    public function assertMatch($test): void
    {
        if (!is_string($test)) {
            throw new Mismatches\TypeMismatch('string', gettype($test), 'Cannot match a Regex over a {{ actual }} type. A {{ expected }} is expected');
        }

        if (!preg_match('/'.$this->value.'/', $test)) {
            throw new Mismatches\ValueMismatch('Value {{ actual }} is not matching the regex expression {{ expected }}', $this->value, $test);
        }
    }
}
