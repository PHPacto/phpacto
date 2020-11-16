<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) Damian Długosz
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

namespace PHPacto\Matcher\Mismatches;

use PHPUnit\Framework\TestCase;

class MismatchCollectionTest extends TestCase
{
    public function test_it_is_countable_and_iterable()
    {
        $miss = new ValueMismatch('A mismatch is expected', true, false);

        $collection = new MismatchCollection([$miss, $miss]);

        self::assertCount(2, $collection);

        foreach ($collection as $miss) {
            self::assertInstanceOf(Mismatch::class, $miss);
        }
    }

    public function test_it_is_convertible_into_array(): MismatchCollection
    {
        $miss = new ValueMismatch('Mismatch', true, false);

        $anotherMiss = new MismatchCollection([$miss, 'another-key' => $miss]);

        $collection = new MismatchCollection(['key' => $miss, $anotherMiss]);

        $expected = [
            'key' => 'Mismatch',
            0 => [
                0 => 'Mismatch',
                'another-key' => 'Mismatch',
            ],
        ];

        self::assertEquals($expected, $collection->toArray());

        return $collection;
    }

    /**
     * @depends test_it_is_convertible_into_array
     */
    public function test_it_is_convertible_into_array_flat(MismatchCollection $collection): MismatchCollection
    {
        $expected = [
            'key' => 'Mismatch',
            '0.0' => 'Mismatch',
            '0.another-key' => 'Mismatch',
        ];

        self::assertEquals($expected, $collection->toArrayFlat());

        return $collection;
    }

    /**
     * @depends test_it_is_convertible_into_array_flat
     */
    public function test_it_counts_all_sub_mismatches(MismatchCollection $collection)
    {
        self::assertEquals(3, $collection->countAll());
    }
}
