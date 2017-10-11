<?php

namespace Bigfoot\PHPacto\Matcher\Mismatches;

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
                'another-key' => 'Mismatch'
            ]
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
