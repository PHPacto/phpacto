<?php

namespace Bigfoot\PHPacto\Matcher\Mismatches;

class KeyNotFoundMismatch extends Mismatch
{
    private $key;

    /**
     * @param string $message
     * @param mixed $expected
     * @param mixed $actual
     */
    public function __construct(string $key)
    {
        $this->message = sprintf('Key `%s` was not found', $key);
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getExpected()
    {
        return $this->key;
    }
}
