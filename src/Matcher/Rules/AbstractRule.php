<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

abstract class AbstractRule implements Rule
{
    protected $value;
    protected $sample;

    public function __construct($value, $sample = null)
    {
        $this->value = $value;
        $this->sample = $sample;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getSample()
    {
        return $this->sample;
    }

    /**
     * Throws exception if an unsupported value is provided
     *
     * @param $value
     * @throws \InvalidArgumentException
     */
    abstract protected function assertSupport($value): void;
}
