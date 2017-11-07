<?php

/*
 * This file is part of PHPacto
 *
 * Copyright (c) 2017  Damian Długosz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bigfoot\PHPacto\Matcher\Mismatches;

class MismatchCollection extends Mismatch implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * @var Mismatch[]
     */
    private $mismatches;

    /**
     * @param Mismatch[] $mismatches
     * @param string     $message
     */
    public function __construct(array $mismatches, string $message = null)
    {
        parent::__construct(str_replace('{{ count }}', count($mismatches), $message ?: '{{ count }} rules are failed'));

        $this->mismatches = $mismatches;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->mismatches as $key => $value) {
            if ($value instanceof self) {
                $result[$key] = $value->toArray();
            } else {
                $result[$key] = $value->getMessage();
            }
        }

        return $result;
    }

    /**
     * @param string     $prefix
     * @param array|null $mismatches
     *
     * @return array
     */
    public function toArrayFlat(string $prefix = null, array $mismatches = null): array
    {
        $result = [];

        if (null !== $prefix) {
            $prefix .= '.';
        }

        if (null === $mismatches) {
            $mismatches = $this->toArray();
        }

        foreach ($mismatches as $key => $mismatch) {
            if (is_array($mismatch)) {
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
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        throw new \Exception('This is cannot be accepted');
    }

    /**
     * @codeCoverageIgnore
     *
     * @param mixed $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->mismatches[$offset]);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        throw new \Exception('This is cannot be accepted');
    }

    public function offsetGet($offset): Mismatch
    {
        return $this->mismatches[$offset];
    }
}
