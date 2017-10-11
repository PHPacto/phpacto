<?php


namespace Bigfoot\PHPacto\Matcher\Mismatches;

class ValueMismatch extends Mismatch
{
    private $actual;
    private $expected;

    /**
     * @param string $message
     * @param mixed $expected
     * @param mixed $actual
     */
    public function __construct(string $message, $expected, $actual)
    {
        $this->message = str_replace(
            ['{{ expected }}', '{{ actual }}'],
            [self::strJoin((array) $expected), self::wrap((string) $actual)],
            $message
        );
        $this->expected = $expected;
        $this->actual = $actual;
    }

    /**
     * @param array $values
     * @param string $glue
     * @return string
     */
    protected static function strJoin(array $values, string $glue = ' or '): string
    {
        $callback = function ($value) {
            return self::wrap((string) $value);
        };

        return implode($glue, array_map($callback, $values));
    }

    /**
     * @param string $value
     * @return string
     */
    protected static function wrap(string $value): string
    {
        return sprintf('`%s`', $value);
    }

    /**
     * @return mixed
     */
    public function getActual()
    {
        return $this->actual;
    }

    /**
     * @return mixed
     */
    public function getExpected()
    {
        return $this->expected;
    }
}
