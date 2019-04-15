<?php

namespace Valet;

class RedisTool
{
    const DEFAULT_VERSION = '5';
    const NAME = 'redis';
    const IMAGE = 'redis';

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
        info('[redis] Restarting');
        $version = $version ?? static::DEFAULT_VERSION;
        $this->stop($version);

        $image = static::IMAGE . ':' . $version;
        $this->docker->installImage($image);
        info('[redis] Starting');

        $this->docker->run(
            static::NAME . '-' . $version,
            $image
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
        info('[redis] Stopping');
        $version = $version ?? static::DEFAULT_VERSION;
        $name = static::NAME . '-' . $version;

        $this->docker->stop($name);
        $this->docker->remove($name);
    }
}
