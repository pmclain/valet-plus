<?php

namespace Valet;

use Valet\Config\Environment;

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
    public function restart()
    {
        info('[redis] Restarting');
        $version = $this->environment->getRequiredRedisVersion() ?? static::DEFAULT_VERSION;
        $this->stop($version);

        $image = static::IMAGE . ':' . $version;
        $this->docker->installImage($image);
        info('[redis] Starting');

        $this->docker->run(
            static::NAME . '-' . $version,
            $image,
            ['6379:6379']
        );
    }

    /**
     * Stop the service.
     *
     * @param string|null $version
     * @throws \Exception
     */
    public function stop($version = null)
    {
        info('[redis] Stopping');
        if (!$version) {
            $version = $this->environment->getRequiredRedisVersion() ?? static::DEFAULT_VERSION;
        }

        $name = static::NAME . '-' . $version;

        $this->docker->stop($name);
        $this->docker->remove($name);
    }
}
