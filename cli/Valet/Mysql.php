<?php

namespace Valet;

use mysqli;
use MYSQLI_ASSOC;
use Valet\Config\Environment;

class Mysql
{
    const DEFAULT_VERSION = '5.7';
    const NAME = 'mysql';
    const IMAGE_MYSQL = 'mysql';
    const IMAGE_MARIADB = 'mariadb';
    const MYSQL_ROOT_PASSWORD = 'root';

    /**
     * @var CommandLine
     */
    private $cli;

    /**
     * @var Docker
     */
    private $docker;

    /**
     * @var Filesystem
     */
    private $files;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var array
     */
    private $systemDatabase = ['sys', 'performance_schema', 'information_schema', 'mysql@5.7'];

    /**
     * @var Mysqli
     */
    private $link = false;

    /**
     * Create a new instance.
     *
     * @param CommandLine   $cli
     * @param Filesystem    $files
     * @param Configuration $configuration
     * @param Docker $docker
     * @param Environment
     */
    public function __construct(
        CommandLine $cli,
        Filesystem $files,
        Configuration $configuration,
        Docker $docker,
        Environment $environment
    ) {
        $this->cli = $cli;
        $this->files = $files;
        $this->configuration = $configuration;
        $this->docker = $docker;
        $this->environment = $environment;
    }

    /**
     * Stop the Mysql service.
     * @param string|null $version
     * @throws \Exception
     */
    public function stop(?string $version = null): void
    {
        info('[mysql] Stopping');
        if (!$version) {
            $version = $this->environment->getRequiredDatabaseVersion() ?? static::DEFAULT_VERSION;
        }
        $name = static::NAME . '-' . $version;

        $this->docker->stop($name);
        $this->docker->remove($name);
    }

    /**
     * Restart the Mysql service.
     * @throws \Exception
     */
    public function restart(): void
    {
        info('[mysql] Restarting');
        $this->stop();
        $this->start();
    }

    /**
     * @throws \Exception
     */
    public function start(): void
    {
        $version = $this->environment->getRequiredDatabaseVersion() ?? static::DEFAULT_VERSION;
        $image = $this->getImageName($version) . ':' . $version;
        $this->docker->installImage($image);

        info('[mysql] Starting');
        $this->docker->run(
            static::NAME . '-' . $version,
            $image,
            ['3306:3306'],
            'dbdata-' . $version . ':/var/lib/mysql',
            ['MYSQL_ROOT_PASSWORD=' . $this->getRootPassword()]
        );
    }

    /**
     * Print table of exists databases.
     */
    public function listDatabases()
    {
        table(['Database'], $this->getDatabases());
    }

    /**
     * Return Mysql connection.
     *
     * @return bool|mysqli
     */
    public function getConnection()
    {
        // if connection already exists return it early.
        if ($this->link) {
            return $this->link;
        }

        // Create connection
        $this->link = new mysqli('127.0.0.1', 'root', $this->getRootPassword());

        // Check connection
        if ($this->link->connect_error) {
            warning('Failed to connect to database');

            return false;
        }

        return $this->link;
    }

    /**
     * Drop current Mysql database & re-import it from file.
     *
     * @param $file
     * @param $database
     */
    public function reimportDatabase($file, $database)
    {
        $this->importDatabase($file, $database, true);
    }

    /**
     * Import Mysql database from file.
     *
     * @param string $file
     * @param string $database
     * @param bool   $dropDatabase
     */
    public function importDatabase($file, $database, $dropDatabase = false)
    {
        $database = $this->getDatabaseName($database);

        // drop database first
        if ($dropDatabase) {
            $this->dropDatabase($database);
        }

        $this->createDatabase($database);

        $gzip = ' | ';
        if (\stristr($file, '.gz')) {
            $gzip = ' | gzip -cd | ';
        }
        $this->cli->passthru('pv ' . \escapeshellarg($file) . $gzip . 'mysql ' . \escapeshellarg($database));
    }

    /**
     * Get current dir name.
     *
     * @return string
     */
    public function getDirName()
    {
        $gitDir = $this->cli->runAsUser('git rev-parse --show-toplevel 2>/dev/null');

        if ($gitDir) {
            return \trim(\basename($gitDir));
        }

        return \trim(\basename(\getcwd()));
    }

    /**
     * Drop Mysql database.
     *
     * @param string $name
     *
     * @return bool|string
     */
    public function dropDatabase($name)
    {
        $name = $this->getDatabaseName($name);

        return $this->query('DROP DATABASE `' . $name . '`') ? $name : false;
    }

    /**
     * Create Mysql database.
     *
     * @param string $name
     *
     * @return bool|string
     */
    public function createDatabase($name)
    {
        $name = $this->getDatabaseName($name);

        return $this->query('CREATE DATABASE IF NOT EXISTS `' . $name . '`') ? $name : false;
    }

    /**
     * Check if database already exists.
     *
     * @param string $name
     *
     * @return bool|\mysqli_result
     */
    public function isDatabaseExists($name)
    {
        $name = $this->getDatabaseName($name);

        $query = $this->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $this->escape($name) . "'", false);

        return (bool) $query->num_rows;
    }

    /**
     * Export Mysql database.
     *
     * @param $filename
     * @param $database
     *
     * @return array
     */
    public function exportDatabase($filename, $database)
    {
        $database = $this->getDatabaseName($database);

        if (!$filename || $filename === '-') {
            $filename = $database . '-' . \date('Y-m-d-His', \time());
        }

        if (!\stristr($filename, '.sql')) {
            $filename = $filename . '.sql.gz';
        }
        if (!\stristr($filename, '.gz')) {
            $filename = $filename . '.gz';
        }

        $this->cli->passthru('mysqldump ' . \escapeshellarg($database) . ' | gzip > ' . \escapeshellarg($filename ?: $database));

        return [
            'database' => $database,
            'filename' => $filename,
        ];
    }

    /**
     * Open Mysql database via Sequel pro.
     *
     * @param string $name
     */
    public function openSequelPro($name = '')
    {
        $tmpName = \tempnam(\sys_get_temp_dir(), 'sequelpro') . '.spf';

        $contents = $this->files->get(__DIR__ . '/../stubs/sequelpro.spf');

        $this->files->putAsUser(
            $tmpName,
            \str_replace(
                ['DB_NAME', 'DB_HOST', 'DB_USER', 'DB_PASS', 'DB_PORT'],
                [$this->getDatabaseName($name), '127.0.0.1', 'root', $this->getRootPassword(), '3306'],
                $contents
            )
        );

        $this->cli->quietly('open ' . $tmpName);
    }

    /**
     * Get database name via name or current dir.
     *
     * @param $database
     *
     * @return string
     */
    protected function getDatabaseName($database = '')
    {
        return $database ?: $this->getDirName();
    }

    /**
     * Get default databases of mysql.
     *
     * @return array
     */
    protected function getSystemDatabase()
    {
        return $this->systemDatabase;
    }

    /**
     * Get exists databases.
     *
     * @return array|bool
     */
    protected function getDatabases()
    {
        $result = $this->query('SHOW DATABASES');

        if (!$result) {
            return false;
        }

        return collect($result->fetch_all(MYSQLI_ASSOC))->reject(function ($row) {
            return \in_array($row['Database'], $this->getSystemDatabase());
        })->map(function ($row) {
            return [$row['Database']];
        })->toArray();
    }

    /**
     * escape string of query via myslqi.
     *
     * @param string $string
     *
     * @return string
     */
    protected function escape($string)
    {
        return \mysqli_real_escape_string($this->getConnection(), $string);
    }

    /**
     * Run Mysql query.
     *
     * @param $query
     * @param bool $escape
     *
     * @return bool|\mysqli_result
     */
    protected function query($query, $escape = true)
    {
        $link = $this->getConnection();

        $query = $escape ? $this->escape($query) : $query;

        return tap($link->query($query), function ($result) use ($link) {
            if (!$result) { // throw mysql error
                warning(\mysqli_error($link));
            }
        });
    }

    /**
     * Returns the stored password from the config. If not configured returns the default root password.
     */
    private function getRootPassword()
    {
        $config = $this->configuration->read();
        if (isset($config['mysql']) && isset($config['mysql']['password'])) {
            return $config['mysql']['password'];
        }

        return self::MYSQL_ROOT_PASSWORD;
    }

    private function getImageName(string $version): string
    {
        if (version_compare($version, 10, '<')) {
            return static::IMAGE_MYSQL;
        }

        return static::IMAGE_MARIADB;
    }
}
