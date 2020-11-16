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

namespace PHPacto;

use PHPacto\Encoder\BodyEncoder;
use PHPacto\Matcher\Mismatches\Mismatch;
use PHPacto\Matcher\Mismatches\MismatchCollection;
use PHPacto\Matcher\Rules\Rule;
use Http\Factory\Discovery\HttpFactory;
use Psr\Http\Message\ResponseInterface;

class PactResponse extends PactMessage implements PactResponseInterface
{
    /**
     * @var Rule
     */
    private $statusCode;

    /**
     * @param Rule[]    $headers
     * @param Rule|null $body
     */
    public function __construct(Rule $statusCode, array $headers = [], $body = null)
    {
        parent::__construct($headers, $body);

        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): Rule
    {
        return $this->statusCode;
    }

    public function assertMatch(ResponseInterface $request)
    {
        $mismatches = [];

        try {
            $this->statusCode->assertMatch($request->getStatusCode());
        } catch (Mismatch $mismatch) {
            $mismatches['STATUS CODE'] = $mismatch;
        }

        try {
            $this->assertMatchHeaders($request);
        } catch (Mismatch $mismatch) {
            $mismatches['HEADERS'] = $mismatch;
        }

        try {
            $this->assertMatchBody($request);
        } catch (Mismatch $mismatch) {
            $mismatches['BODY'] = $mismatch;
        }

        if ($mismatches) {
            throw new MismatchCollection($mismatches, 'Response does not match');
        }
    }

    public function getSample(): ResponseInterface
    {
        $statusCode = $this->statusCode->getSample();

        $headers = $this->getSampleHeaders();
        $body = $this->getSampleBody();

        $response = HttpFactory::responseFactory()->createResponse($statusCode);

        if (null !== $body) {
            $stream = HttpFactory::streamFactory()->createStreamFromFile('php://memory', 'w');
            $stream->write(BodyEncoder::encode($body, $this->getContentType()));

            $response = $response->withBody($stream);
        }

        foreach ($headers as $key => $value) {
            $response = $response->withAddedHeader($key, $value);
        }

        return $response;
    }
}
