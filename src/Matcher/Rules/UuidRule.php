<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;
use Ramsey\Uuid\Uuid;

class UuidRule extends AbstractRule
{
    private const PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    public function __construct($sample = null)
    {
        if ($sample === null) {
            $sample = Uuid::uuid4()->toString();
        }

        $this->assertSupport($sample);

        parent::__construct(null, $sample);
    }

    protected function assertSupport($value): void
    {
        $this->assertMatch($value);
    }

    public function assertMatch($test): void
    {
        if (!is_string($test)) {
            throw new Mismatches\TypeMismatch('string', gettype($test));
        }

        if (!preg_match(self::PATTERN, $test)) {
            throw new Mismatches\ValueMismatch('Value {{ actual }} is not a valid UUID, expecting a string like {{ expected }}', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', $test);
        }
    }
}
