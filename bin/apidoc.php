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

use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Loader\PactLoader;
use EXSyst\Component\Swagger;

require __DIR__ . '/bootstrap.php';

$pacts = (new PactLoader(SerializerFactory::getInstance()))
    ->loadFromDirectory(CONTRACTS_DIR);

if (0 === count($pacts)) {
    throw new \Exception(sprintf('No Pacts found in %s', CONTRACTS_DIR));
}

$swagger = new Swagger\Swagger();
$swPaths = $swagger->getPaths();

$swagger->getInfo()->setTitle('PHPacto Swagger generator');
$swagger->getInfo()->setVersion(reset($pacts)->getVersion());

foreach ($pacts as $pact) {
    $request = $pact->getRequest();
    $requestContentType = @$request->getHeaders()['Content-Type'];

    $swRequestContentType = $requestContentType ? $requestContentType->getSample() : '*';

    $response = $pact->getResponse();
    $responseContentType = @$response->getHeaders()['Content-Type'];

    $swResponseContentType = $responseContentType ? $responseContentType->getSample() : '*';

    $operation = new Swagger\Operation([
        'summary' => $pact->getDescription(),
        'consumes' => $requestContentType ? [$requestContentType->getSample()] : null,
        'produces' => $responseContentType ? [$responseContentType->getSample()] : null,
        'requestBody' => [
            'content' => [
                $swRequestContentType => [
//                    'schema' => [
//                        'type' => 'string' 'integer' 'boolean' 'object',
//                        'format' => 'int32' 'int64' 'date-time' 'binary',
//                        'enum' => ['A', 'B', 'C'],
//                    ],
                    'schema' => [
                        'type' => 'object',
                        'example' => $request->getSampleBody(),
                    ],
                ],
            ],
        ],
    ]);

    $swResponse = new Swagger\Response([
        'description' => $pact->getDescription(),
        'examples' => [
            $swResponseContentType => [
//                'schema' => [
//                    'type' => 'string' 'integer' 'boolean' 'array' 'object',
//                    'format' => 'int32' 'int64' 'date-time' 'binary',
//                    'enum' => ['A', 'B', 'C'],
//                ],
                $response->getSampleBody(),
            ],
        ],
//        'examples' => $request->getSampleBody(),
    ]);

    foreach ($response->getSampleHeaders() as $key => $value) {
        if ('c' === strtolower($key)) {
            continue;
        }
        $header = new Swagger\Header([
            'type' => gettype($value),
        ]);
        $swResponse->getHeaders()->set($key, $header);
    }

    $operation->getResponses()->set($response->getStatusCode()->getSample(), $swResponse);

    $method = strtolower($request->getMethod()->getSample());
    $path = strtolower($request->getPath()->getSample());

    $swPath = new Swagger\Path();
    $swPath->setOperation($method, $operation);
    $swPaths->set($path, $swPath);
}

header('Content-Type: application/json');
echo json_encode($swagger->toArray()) . "\n";
