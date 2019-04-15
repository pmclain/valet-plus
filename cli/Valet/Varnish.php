<?php

namespace Valet;

class Varnish
{
    const DEFAULT_VERSION = '4';
    const NAME = 'varnish';

    /**
     * @var CommandLine
     */
    private $cli;

    /**
     * @var Filesystem
     */
    private $file;

    /**
     * Create a new instance.
     *
     * @param CommandLine $cli
     * @param Filesystem $file
     */
    public function __construct(
        CommandLine $cli,
        Filesystem $file
    ) {
        $this->cli = $cli;
        $this->file = $file;
    }

    public function install()
    {
        info('[varnish] Installing');
        $dockerFile = $this->file->realpath(__DIR__ . '/../../cli/stubs/varnish');
        $this->cli->runAsUser('docker build ' . $dockerFile . ' -t pmclain/m2-varnish');
    }

    /**
     * Restart the service.
     *
     * @param string|null $version
     * @return void
     */
    public function restart(?string $version = null)
    {
        info('[varnish] Restarting');
        $version = $version ?? static::DEFAULT_VERSION;
        $this->stop($version);
        info('[rabbitmq] Starting');
        $command = sprintf(
            'docker run -d --name %s-%s -p "6081:6081" -p "6085:6085" -e "BACKENDS_PORT=8080" pmclain/m2-varnish',
            static::NAME,
            $version
        );

        $this->cli->quietlyAsUser($command);
    }

    /**
     * Stop the service.
     *
     * @param string|null $version
     * @return void
     */
    public function stop(?string $version = null)
    {
        info('[varnish] Stopping');
        $version = $version ?? static::DEFAULT_VERSION;
        $this->cli->quietlyAsUser('docker stop ' . static::NAME . '-' . $version);
        $this->cli->quietlyAsUser('docker rm ' . static::NAME . '-' . $version);
    }
}
