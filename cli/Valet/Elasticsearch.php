<?php

namespace Valet;

class Elasticsearch
{
    const DEFAULT_VERSION = '5.2';
    const NAME = 'elasticsearch';

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
        info('[elasticsearch] Restarting');
        $version = $version ?? static::DEFAULT_VERSION;
        $this->stop($version);
        info('[elasticsearch] Starting');
        $command = sprintf(
            'docker run -d --name %s-%s -p 9200:9200 -v "esdata-%s:/usr/share/elasticsearch/data" -e "discovery.type=single-node" elasticsearch:%s',
            static::NAME,
            $version,
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
        info('[elasticsearch] Stopping');
        $version = $version ?? static::DEFAULT_VERSION;
        $this->cli->quietlyAsUser('docker stop ' . static::NAME . '-' . $version);
        $this->cli->quietlyAsUser('docker rm ' . static::NAME . '-' . $version);
    }
}
