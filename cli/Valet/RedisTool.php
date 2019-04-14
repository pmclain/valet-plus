<?php

namespace Valet;

class RedisTool
{
    const DEFAULT_VERSION = '5';
    const NAME = 'redis';

    /**
     * @var CommandLine
     */
    private $cli;

    /**
     * Create a new instance.
     *
     * @param CommandLine $cli
     */
    public function __construct(
        CommandLine $cli
    ) {
        $this->cli = $cli;
    }

    /**
     * Restart the service.
     *
     * @param string|null $version
     * @return void
     */
    public function restart(?string $version = null)
    {
        info('[redis] Restarting');
        $version = $version ?? static::DEFAULT_VERSION;
        $this->stop($version);
        info('[redis] Starting');
        $command = sprintf(
            'docker run -d --name %s-%s -p 6379:6379 redis:%s',
            static::NAME,
            $version,
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
        info('[redis] Stopping');
        $version = $version ?? static::DEFAULT_VERSION;
        $this->cli->quietlyAsUser('docker stop ' . static::NAME . '-' . $version);
        $this->cli->quietlyAsUser('docker rm ' . static::NAME . '-' . $version);
    }
}
