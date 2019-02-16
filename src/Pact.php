<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2019  Damian Długosz
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

namespace Bigfoot\PHPacto;

use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;

class Pact implements PactInterface
{
    /**
     * @var PactRequestInterface
     */
    private $request;

    /**
     * @var PactResponseInterface
     */
    private $response;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $version;

    public function __construct(PactRequestInterface $request, PactResponseInterface $response, string $description = '', string $version = PactInterface::VERSION)
    {
        $this->request = $request;
        $this->response = $response;
        $this->description = $description;
        $this->version = $version;

        $this->assertVersionIsCompatible($version);

        $mismatches = [];

        try {
            // Assert that sample is matching its own rules
            $sample = $request->getSample();
            $request->assertMatch($sample);
        } catch (Mismatch $mismatch) {
            $mismatches['REQUEST'] = $mismatch;
        }

        try {
            // Assert that sample is matching its own rules
            $sample = $response->getSample();
            $response->assertMatch($sample);
        } catch (Mismatch $mismatch) {
            $mismatches['RESPONSE'] = $mismatch;
        }

        if ($mismatches) {
            throw new MismatchCollection($mismatches, 'Pact is not valid');
        }
    }

    public function getRequest(): PactRequestInterface
    {
        return $this->request;
    }

    public function getResponse(): PactResponseInterface
    {
        return $this->response;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    private function assertVersionIsCompatible($version)
    {
        if (version_compare($version, PactInterface::VERSION, '>')) {
            throw new \Exception(sprintf('Unsupported Pact version `%s`. Current supported version is `%s` and newer', $version, PactInterface::VERSION));
        }
    }
}
