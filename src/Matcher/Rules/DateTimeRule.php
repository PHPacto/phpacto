<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class DateTimeRule extends AbstractRule
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
        }

        if ($value == '') {
            throw new Mismatches\TypeMismatch('string' , 'empty');
        }
    }

    public function assertMatch($test): void
    {
        if (! $test instanceof \DateTimeInterface
            && ! \DateTimeImmutable::createFromFormat($this->value, $test) instanceof \DateTimeInterface
        ) {
            throw new Mismatches\ValueMismatch('Cannot convert value {{ actual }} into a valid DateTime using {{ expected }} format', $this->value, $test);
        }
    }

    public function getSample()
    {
        return \DateTimeImmutable::createFromFormat($this->value, $this->sample);
    }
}
