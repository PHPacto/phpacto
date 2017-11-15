<?php

namespace Bigfoot\PHPacto\Test\PHPUnit\Constraint;

use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use Bigfoot\PHPacto\PactInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\Constraint as PHPUnitConstraint;
use PHPUnit\Util\InvalidArgumentHelper;
use Psr\Http\Message\RequestInterface;

class PactMatchesRequest extends PHPUnitConstraint
{
    /**
     * @var PactInterface
     */
    protected $pact;

    /**
     * @param PactInterface $pact
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct($pact)
    {
        parent::__construct();

        if (!$pact instanceof PactInterface) {
            throw InvalidArgumentHelper::factory(1, PactInterface::class);
        }

        $this->pact = $pact;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate($request, $description = '', $returnResult = false)
    {
        try {
            $this->pact->getRequest()->assertMatch($request);
        } catch (Mismatch $mismatch) {
            if ($returnResult) {
                return false;
            }

            $this->failPactMatching($request, $mismatch, $description);
        }

        return true;
    }

    protected function failPactMatching(RequestInterface $request, Mismatch $mismatch, string $description = null)
    {
        $failureDescription = \sprintf(
            'Failed asserting that %s',
            $this->failureDescription($request, $mismatch)
        );

        $additionalFailureDescription = $this->additionalFailureDescription($request);

        if ($additionalFailureDescription) {
            $failureDescription .= "\n" . $additionalFailureDescription;
        }

        if (!empty($description)) {
            $failureDescription = $description . "\n" . $failureDescription;
        }

        throw new AssertionFailedError(trim($failureDescription));
    }

    /**
     * {@inheritdoc}
     */
    protected function failureDescription($other, Mismatch $mismatch = null)
    {
        if ($mismatch instanceof MismatchCollection) {
            $mimatchesArray = $mismatch->toArrayFlat();

            return sprintf(
                "%s (%d rules failed)\n%s",
                $this->toString(),
                count($mimatchesArray),
                implode("\n", $mimatchesArray)
            );
        }

        return $this->toString(). ' ' . $mismatch->getMessage();
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return sprintf('Pact `%s` match request', $this->pact->getDescription());
    }
}
