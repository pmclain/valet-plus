<?php

namespace Valet;

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
     * Create a new instance.
     *
     * @param Docker $docker
     */
    public function __construct(
        Docker $docker
    ) {
        $this->docker = $docker;
    }

    /**
     * Restart the service.
     *
     * @param string|null $version
     * @throws \Exception
     */
    public function restart(?string $version = null): void
    {
        info('[rabbitmq] Restarting');
        $version = $version ?? static::DEFAULT_VERSION;
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
        $version = $version ?? static::DEFAULT_VERSION;
        $name = static::NAME . '-' . $version;

        $this->docker->stop($name);
        $this->docker->remove($name);
    }
}
