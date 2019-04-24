<?php

namespace Valet;

use Valet\Config\Environment;

class RabbitMq
{
    const DEFAULT_VERSION = '3.7';
    const NAME = 'rabbitmq';
    const IMAGE = 'rabbitmq';

    /**
     * @var Docker
     */
    private $docker;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * Create a new instance.
     *
     * @param Docker $docker
     * @param Environment $environment
     */
    public function __construct(
        Docker $docker,
        Environment $environment
    ) {
        $this->docker = $docker;
        $this->environment = $environment;
    }

    /**
     * Restart the service.
     *
     * @throws \Exception
     */
    public function restart(): void
    {
        info('[rabbitmq] Restarting');
        $version = $this->environment->getRequiredRabbitMqVersion() ?? static::DEFAULT_VERSION;
        $this->stop($version);

        $image = static::IMAGE . ':' . $version . '-management';
        $this->docker->installImage($image);
        info('[rabbitmq] Starting');

        $this->docker->run(
            static::NAME . '-' . $version,
            $image,
            ['15672:15672', '5672:5672'],
            '',
            ['RABBITMQ_DEFAULT_USER=guest', 'RABBITMQ_DEFAULT_PASSWORD=guest']
        );
    }

    /**
     * Stop the service.
     *
     * @param string|null $version
     * @throws \Exception
     */
    public function stop(?string $version = null): void
    {
        info('[elasticsearch] Stopping');
        if (!$version) {
            $version = $this->environment->getRequiredElasticsearchVersion() ?? static::DEFAULT_VERSION;
        }
        $name = static::NAME . '-' . $version;

        $this->docker->stop($name);
        $this->docker->remove($name);
    }
}
