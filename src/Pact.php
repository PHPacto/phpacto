<?php

namespace Bigfoot\PHPacto;

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
    }

    private function assertVersionIsCompatible($version)
    {
        if (version_compare($version, PactInterface::VERSION, '>')) {
            throw new \Exception(sprintf('Unsupported Pact version `%s`. Current supported version is `%s` and newer', $version, PactInterface::VERSION));
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
}
