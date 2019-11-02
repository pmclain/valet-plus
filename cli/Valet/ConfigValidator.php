<?php
declare(strict_types=1);

namespace Valet;

use Symfony\Component\EventDispatcher\Event;
use Valet\Config\Environment;

class ConfigValidator
{
    private $envConfig;

    private $phpFpm;

    private $docker;

    public function __construct(
        Environment $envConfig,
        PhpFpm $phpFpm,
        Docker $docker
    ) {
        $this->envConfig = $envConfig;
        $this->phpFpm = $phpFpm;
        $this->docker = $docker;
    }

    public function validate(Event $event)
    {
        $this->validatePhpVersion();

        // Don't error when stopping services
        $command = $event->getCommand()->getName();
        if ($command === 'stop') {
            return;
        }

        $this->validateRedisVersion();
        $this->validateDatabaseVersion();
        $this->validateElasticsearchVersion();
        $this->validateRabbitMqVersion();
    }

    private function validatePhpVersion()
    {
        $currentPhp = $this->phpFpm->linkedPhp();
        $requiredVersion = $this->envConfig->getRequiredPhpVersion();
        if ($requiredVersion && $currentPhp !== $requiredVersion) {
            warning(sprintf('PHP %s is required by current project configuration. Attempting to switch.', $requiredVersion));
            $this->phpFpm->switchTo($requiredVersion);
        }
    }

    /**
     * @throws \Exception
     */
    private function validateRedisVersion()
    {
        $running = $this->docker->getServiceVersion(RedisTool::NAME);
        $required = $this->envConfig->getRequiredRedisVersion();
        if (empty($required)) {
            return;
        }

        if ($running && $running !== $required) {
            $this->serviceVersionException(RedisTool::NAME, $running);
        }
    }

    /**
     * @throws \Exception
     */
    private function validateDatabaseVersion()
    {
        $running = $this->docker->getServiceVersion(Mysql::NAME);
        $required = $this->envConfig->getRequiredDatabaseVersion();
        if (empty($required)) {
            return;
        }

        if ($running && $running !== $required) {
            $this->serviceVersionException(Mysql::NAME, $running);
        }
    }

    /**
     * @throws \Exception
     */
    private function validateElasticsearchVersion()
    {
        $running = $this->docker->getServiceVersion(Elasticsearch::NAME);
        $required = $this->envConfig->getRequiredElasticsearchVersion();
        if (empty($required)) {
            return;
        }

        if ($running && $running !== $required) {
            $this->serviceVersionException(Elasticsearch::NAME, $running);
        }
    }

    /**
     * @throws \Exception
     */
    private function validateRabbitMqVersion()
    {
        $running = $this->docker->getServiceVersion(RabbitMq::NAME);
        $required = $this->envConfig->getRequiredRabbitMqVersion();
        if (empty($required)) {
            return;
        }

        if ($running && $running !== $required) {
            $this->serviceVersionException(RabbitMq::NAME, $running);
        }
    }

    /**
     * @param string $serviceName
     * @param string $version
     * @throws \Exception
     */
    private function serviceVersionException(string $serviceName, string $version)
    {
        throw new \Exception(sprintf(
            'The running version of %s does not match project requirements. To stop this service run:' . PHP_EOL
            . 'valet stop %s:%s',
            $serviceName,
            $serviceName,
            $version
        ));
    }
}
