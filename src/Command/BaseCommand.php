<?php

namespace Bigfoot\PHPacto\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Serializer\Serializer;

abstract class BaseCommand extends Command
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $defaultContractsDir;

    public function __construct(Serializer $serializer, string $defaultContractsDir = null)
    {
        $this->serializer = $serializer;
        $this->defaultContractsDir = $defaultContractsDir;

        parent::__construct();
    }
}
