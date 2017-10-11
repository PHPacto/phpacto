<?php

namespace Bigfoot\PHPacto\Matcher\Mismatches;

class MismatchCollection extends Mismatch implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * @var Mismatch[]
     */
    private $mismatches;

    /**
     * @param Mismatch[] $mismatches
     * @param string $message
     */
    public function __construct(array $mismatches, string $message = null)
    {
        parent::__construct(str_replace('{{ count }}', count($mismatches),$message ?: '{{ count }} rules are failed'));

        $this->mismatches = $mismatches;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->mismatches as $key => $value) {
            if ($value instanceof MismatchCollection) {
                $result[$key] = $value->toArray();
            } else {
                $result[$key] = $value->getMessage();
            }
        }

        return $result;
    }

    /**
     * @param string $prefix
     * @param array|null $mismatches
     * @return array
     */
    public function toArrayFlat(string $prefix = null, array $mismatches = null): array
    {
        $result = [];

        if ($prefix !== null) {
            $prefix .= '.';
        }

        if ($mismatches === null) {
            $mismatches = $this->toArray();
        }

        foreach ($mismatches as $key => $mismatch) {
            if (is_array($mismatch)){
                $result = array_merge($result, $this->toArrayFlat($prefix.$key, $mismatch));
            } else {
                $result[$prefix.$key] = $mismatch;
            }
        }

        return $result;
    }

    public function count()
    {
        return count($this->mismatches);
    }

    public function countAll()
    {
        return count($this->toArrayFlat());
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->mismatches);
    }

    /**
     * @codeCoverageIgnore
     */
    public function offsetSet($offset, $value) {
        throw new \Exception('This is cannot be accepted');
    }

    /**
     * @codeCoverageIgnore
     */
    public function offsetExists($offset): bool {
        return isset($this->mismatches[$offset]);
    }

    /**
     * @codeCoverageIgnore
     */
    public function offsetUnset($offset) {
        throw new \Exception('This is cannot be accepted');
    }

    public function offsetGet($offset): Mismatch {
        return $this->mismatches[$offset];
    }
}
