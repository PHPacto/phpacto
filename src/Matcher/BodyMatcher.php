<?php

namespace Bigfoot\PHPacto\Matcher;

use Bigfoot\PHPacto\BodyEncoder;
use Bigfoot\PHPacto\Matcher\Mismatches;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Psr\Http\Message\MessageInterface;

class BodyMatcher implements MessageMatcher
{
    public function assertMatch($rules, MessageInterface $message): void
    {
        $contentType = $message->getHeaderLine('Content-Type');
        $body = BodyEncoder::decode((string) $message->getBody(), $contentType);

        switch (true) {
            case is_string($body):
                $this->matchBodySting($rules, $body);
                break;

            case is_array($body):
                $this->matchBodyArray($rules, $body);
                break;

            default:
                throw new \Exception('Body should be a Rule or an array of Rules');
        }
    }

    private function matchBodySting($rules, string $body): void
    {
        if ($rules instanceof Rule) {
            $rules->assertMatch($body);
        } elseif (is_array($rules)) {
            $mismatches = [];

            /** @var Rule $rule */
            foreach ($rules as $rule) {
                try {
                    $rule->assertMatch($body);
                } catch (Mismatches\Mismatch $mismatch) {
                    $mismatches[] = $mismatch;
                }
            }

            if ($mismatches) {
                throw new Mismatches\MismatchCollection($mismatches, 'Body does not match');
            }
        } else {
            throw new \Exception('Body should be a Rule or an array of Rules');
        }
    }

    private function matchBodyArray($rules, array $body): void
    {
        if ($rules instanceof Rule) {
            $rules->assertMatch($body);
        } elseif (is_array($rules)) {
            $mismatches = [];

            /** @var Rule|Rule[] $rule */
            foreach ($rules as $key => $rule) {
                if (!array_key_exists($key, $body)) {
                    $mismatches[$key] = new Mismatches\KeyNotFoundMismatch($key);
                    continue;
                }

                try {
                    if ($rule instanceof Rule) {
                        $rule->assertMatch($body[$key]);
                    } elseif (is_array($rule)) {
                        $this->matchBodyArray($rule, $body[$key]);
                    } else {
                        throw new \Exception('Body should be a Rule or an array of Rules');
                    }
                } catch (Mismatches\Mismatch $mismatch) {
                    $mismatches[$key] = $mismatch;
                }
            }

            if ($mismatches) {
                throw new Mismatches\MismatchCollection($mismatches, 'Body does not match');
            }
        } else {
            throw new \Exception('Body should be a Rule or an array of Rules');
        }
    }
}
