<?php
declare(strict_types=1);

namespace Valet\Config\Loader;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Valet\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Reads php and service versions from magento cloud config files
 */
class Magento
{
    const FILENAME_PHP = '.magento.app.yaml';
    const FILENAME_SERVICES = '.magento/services.yaml';

    const PATH_PHP_VERSION = '[type]';
    const PATH_REDIS_VERSION = '[redis][type]';
    const PATH_ELASTICSEARH_VERSION = '[elasticsearch][type]';
    const PATH_DATABASE_VERSION = '[mysql][type]';
    const PATH_RABBITMQ_VERSION = '[rabbitmq][type]';

    /**
     * @var Filesystem
     */
    private $file;

    /**
     * @var array
     */
    private $configs = [];

    public function __construct(
        Filesystem $file
    ) {
        $this->file = $file;
    }

    public function getPhpVersion()
    {
        $path = $this->getExpectedConfigPath(static::FILENAME_PHP);
        if (!$this->file->exists($path)) {
            return null;
        }

        $rawValue = $this->getValue($path, static::PATH_PHP_VERSION);
        $version = explode(':', $rawValue)[1] ?? null;

        return $version;
    }

    public function getRedisVersion()
    {
        $path = $this->getExpectedConfigPath(static::FILENAME_SERVICES);
        if (!$this->file->exists($path)) {
            return null;
        }

        $rawValue = $this->getValue($path, static::PATH_REDIS_VERSION);
        $version = explode(':', $rawValue)[1] ?? null;

        return $version;
    }

    public function getDatabaseVersion()
    {
        $path = $this->getExpectedConfigPath(static::FILENAME_SERVICES);
        if (!$this->file->exists($path)) {
            return null;
        }

        $rawValue = $this->getValue($path, static::PATH_DATABASE_VERSION);
        $version = explode(':', $rawValue)[1] ?? null;

        return $version;
    }

    public function getElasticsearchVersion()
    {
        $path = $this->getExpectedConfigPath(static::FILENAME_SERVICES);
        if (!$this->file->exists($path)) {
            return null;
        }

        $rawValue = $this->getValue($path, static::PATH_ELASTICSEARH_VERSION);
        $version = explode(':', $rawValue)[1] ?? null;

        return $version;
    }

    public function getRabbitMqVersion()
    {
        $path = $this->getExpectedConfigPath(static::FILENAME_SERVICES);
        if (!$this->file->exists($path)) {
            return null;
        }

        $rawValue = $this->getValue($path, static::PATH_RABBITMQ_VERSION);
        $version = explode(':', $rawValue)[1] ?? null;

        return $version;
    }

    private function getExpectedConfigPath(string $filename): string
    {
        return getcwd() . DIRECTORY_SEPARATOR . $filename;
    }

    private function getValue(string $filepath, string $configPath): string
    {
        $config = $this->loadConfig($filepath);
        $propertyAccessor = new PropertyAccessor();

        return $propertyAccessor->getValue($config, $configPath) ?? '';
    }

    private function loadConfig(string $path): array
    {
        if (!isset($this->configs[$path])) {
            $this->configs[$path] = Yaml::parseFile($path);
        }

        return $this->configs[$path];
    }
}
