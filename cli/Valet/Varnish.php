<?php

namespace Valet;

class Varnish
{
    const DEFAULT_VERSION = '4';
    const NAME = 'varnish';
    const IMAGE = 'pmclain/m2-varnish';

    /**
     * @var Docker
     */
    private $docker;

    /**
     * @var Filesystem
     */
    private $file;

    /**
     * Create a new instance.
     *
     * @param Docker $docker
     * @param Filesystem $file
     */
    public function __construct(
        Docker $docker,
        Filesystem $file
    ) {
        $this->docker = $docker;
        $this->file = $file;
    }

    public function install()
    {
        info('[varnish] Installing');
        $dockerFile = $this->file->realpath(__DIR__ . '/../../cli/stubs/varnish');
        $this->docker->build($dockerFile, static::IMAGE);
    }

    /**
     * Restart the service.
     *
     * @throws \Exception
     */
    public function restart()
    {
        info('[varnish] Restarting');
        $this->stop();

        $this->install();
        info('[varnish] Starting');

        $this->docker->run(
            static::NAME . '-' . static::DEFAULT_VERSION,
            static::IMAGE,
            ['6081:6081', '6085:6085'],
            '',
            ['BACKENDS_PORT=8080']
        );
    }

    /**
     * Stop the service.
     *
     * @throws \Exception
     */
    public function stop()
    {
        info('[varnish] Stopping');
        $name = static::NAME . '-' . static::DEFAULT_VERSION;

        $this->docker->stop($name);
        $this->docker->remove($name);
    }
}
