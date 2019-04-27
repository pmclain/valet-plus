<?php
declare(strict_types=1);

namespace Valet\Config\Loader;

use Valet\Filesystem;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Yaml\Yaml;

class Valet
{
    private const FILENAME = '.valet.yml';

    private const PATH_PHP_VERSION = '[php]';
    private const PATH_REDIS_VERSION = '[redis]';
    private const PATH_ELASTICSEARH_VERSION = '[elasticsearch]';
    private const PATH_DATABASE_VERSION = '[mysql]';
    private const PATH_RABBITMQ_VERSION = '[rabbitmq]';

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

    public function getPhpVersion(): ?string
    {
        $path = $this->getExpectedConfigPath(static::FILENAME);
        if (!$this->file->exists($path)) {
            return null;
        }

        return $this->getValue($path, static::PATH_PHP_VERSION);
    }

    public function getRedisVersion(): ?string
    {
        $path = $this->getExpectedConfigPath(static::FILENAME);
        if (!$this->file->exists($path)) {
            return null;
        }

        return $this->getValue($path, static::PATH_REDIS_VERSION);
    }

    public function getDatabaseVersion(): ?string
    {
        $path = $this->getExpectedConfigPath(static::FILENAME);
        if (!$this->file->exists($path)) {
            return null;
        }

        return $this->getValue($path, static::PATH_DATABASE_VERSION);
    }

    public function getElasticsearchVersion(): ?string
    {
        $path = $this->getExpectedConfigPath(static::FILENAME);
        if (!$this->file->exists($path)) {
            return null;
        }

        return $this->getValue($path, static::PATH_ELASTICSEARH_VERSION);
    }

    public function getRabbitMqVersion(): ?string
    {
        $path = $this->getExpectedConfigPath(static::FILENAME);
        if (!$this->file->exists($path)) {
            return null;
        }

        return $this->getValue($path, static::PATH_RABBITMQ_VERSION);
    }

    private function getExpectedConfigPath(string $filename): string
    {
        return getcwd() . DIRECTORY_SEPARATOR . $filename;
    }

    private function getValue(string $filepath, string $configPath): string
    {
        $config = $this->loadConfig($filepath);
        $propertyAccessor = new PropertyAccessor();

        return $propertyAccessor->getValue($config, $configPath);
    }

    private function loadConfig(string $path): array
    {
        if (!isset($this->configs[$path])) {
            $this->configs[$path] = Yaml::parseFile($path);
        }

        return $this->configs[$path];
    }
}
