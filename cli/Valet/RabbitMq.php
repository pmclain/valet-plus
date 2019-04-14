<?php

namespace Valet;

class RabbitMq
{
    const DEFAULT_VERSION = '3.7';
    const NAME = 'rabbitmq';

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
        info('[rabbitmq] Restarting');
        $version = $version ?? static::DEFAULT_VERSION;
        $this->stop($version);
        info('[rabbitmq] Starting');
        $command = sprintf(
            'docker run -d --name %s-%s -p "15672:15672" -p "5672:5672" -e "RABBITMQ_DEFAULT_USER=guest" -e "RABBITMQ_DEFAULT_PASSWORD=guest" rabbitmq:%s-management',
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
        info('[rabbitmq] Stopping');
        $version = $version ?? static::DEFAULT_VERSION;
        $this->cli->quietlyAsUser('docker stop ' . static::NAME . '-' . $version);
        $this->cli->quietlyAsUser('docker rm ' . static::NAME . '-' . $version);
    }
}
