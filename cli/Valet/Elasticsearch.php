<?php

namespace Valet;

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
        info('[elasticsearch] Restarting');
        $version = $version ?? static::DEFAULT_VERSION;
        $this->stop($version);

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
        $version = $version ?? static::DEFAULT_VERSION;
        $name = static::NAME . '-' . $version;

        $this->docker->stop($name);
        $this->docker->remove($name);
    }
}
