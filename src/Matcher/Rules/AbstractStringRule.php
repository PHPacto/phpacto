<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

abstract class AbstractStringRule extends AbstractRule
{
    /**
     * @var boolean
     */
    protected $caseSensitive;

    public function __construct($value, $sample = null, bool $caseSensitive = false)
    {
        $this->assertSupport($value);

        parent::__construct($value, $sample);

        $this->caseSensitive = $caseSensitive;

        if (null !== $sample) {
            $this->assertMatch($sample);
        }
    }

    protected function assertSupport($value): void
    {
        if (!is_string($value)) {
            throw new Mismatches\TypeMismatch('string', gettype($value));
        }
    }

    public function assertMatch($test): void
    {
        self::assertSupport($test);
    }

    public function isCaseSensitive(): bool
    {
        return $this->caseSensitive;
    }
}
