<?php

namespace Bigfoot\PHPacto\Matcher;

use Bigfoot\PHPacto\Matcher\Mismatches;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Psr\Http\Message\MessageInterface;

class HeadersMatcher implements MessageMatcher
{
    public function assertMatch($rules, MessageInterface $message): void
    {
        $expectedHeaders = $this->normalizeKeys($rules);
        $actualHeaders = $this->normalizeKeys($message->getHeaders());

        $this->compareHeaders($expectedHeaders, $actualHeaders);
    }

    private function normalizeKeys($headers)
    {
        return array_change_key_case($headers, CASE_LOWER);
    }

    private function compareHeaders($rules, array $headers): void
    {
        $mismatches = [];

        /** @var Rule $rule */
        foreach ($rules as $key => $rule) {
            if (!array_key_exists($key, $headers)) {
                $mismatches[$key] = new Mismatches\KeyNotFoundMismatch($key);
                continue;
            }

            if (is_array($headers[$key]) && count($headers[$key]) == 1) {
                $headers[$key] = $headers[$key][0];
            }

            try {
                if ($rule instanceof Rule) {
                    $rule->assertMatch($headers[$key]);
                } elseif (is_array($rule)) {
                    $this->compareHeaders($rule, $headers[$key]);
                } else {
                    throw new \Exception('Headers should be a Rule or an array of Rules');
                }
            } catch (Mismatches\Mismatch $mismatch) {
                $mismatches[] = $mismatch;
            }
        }

        if ($mismatches) {
            throw new Mismatches\MismatchCollection($mismatches, '{{ count }} Headers do not match');
        }
    }
}
