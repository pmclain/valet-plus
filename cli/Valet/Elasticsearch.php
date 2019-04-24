<?php

namespace Valet;

use Valet\Config\Environment;

class Elasticsearch
{
    const DEFAULT_VERSION = '5.2';
    const NAME = 'elasticsearch';
    const IMAGE = 'elasticsearch';

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
        info('[elasticsearch] Restarting');
        $version = $this->environment->getRequiredElasticsearchVersion() ?? static::DEFAULT_VERSION;
        $this->stop();

        $image = static::IMAGE . ':' . $version;
        $this->docker->installImage($image);
        info('[elasticsearch] Starting');

        $this->docker->run(
            static::NAME . '-' . $version,
            $image,
            ['9200:9200'],
            'esdata-' . $version . ':/usr/share/elasticsearch/data',
            ['discovery.type=single-node']
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
