<?php
declare(strict_types=1);

namespace Valet\Config;

use Valet\Config\Loader\Magento;
use Valet\Config\Loader\Valet;

class Environment
{
    /**
     * @var Magento
     */
    private $magento;

    /**
     * @var Valet
     */
    private $valet;

    public function __construct(
        Magento $magento,
        Valet $valet
    ) {
        $this->magento = $magento;
        $this->valet = $valet;
    }

    public function getRequiredPhpVersion(): ?string
    {
        $version = $this->magento->getPhpVersion();
        if (!$version) {
            $version = $this->valet->getPhpVersion();
        }

        return $version;
    }

    public function getRequiredRedisVersion(): ?string
    {
        $version = $this->magento->getRedisVersion();
        if (!$version) {
            $version = $this->valet->getRedisVersion();
        }

        return $version;
    }

    public function getRequiredDatabaseVersion(): ?string
    {
        $version = $this->magento->getDatabaseVersion();
        if (!$version) {
            $version = $this->valet->getDatabaseVersion();
        }

        return $version;
    }

    public function getRequiredElasticsearchVersion(): ?string
    {
        $version = $this->magento->getElasticsearchVersion();
        if (!$version) {
            $version = $this->valet->getElasticsearchVersion();
        }

        return $version;
    }

    public function getRequiredRabbitMqVersion(): ?string
    {
        $version = $this->magento->getRabbitMqVersion();
        if (!$version) {
            $version = $this->valet->getRabbitMqVersion();
        }

        return $version;
    }
}
