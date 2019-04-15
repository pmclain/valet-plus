<?php
declare(strict_types=1);

namespace Valet;

class Docker
{
    /**
     * @var CommandLine
     */
    private $cli;

    /**
     * @param CommandLine $cli
     */
    public function __construct(
        CommandLine $cli
    ) {
        $this->cli = $cli;
    }

    /**
     * @param string $name
     * @param string $image
     * @param array $ports
     * @param string $volume
     * @param array $env
     * @throws \Exception
     */
    public function run(
        string $name,
        string $image,
        array $ports = [],
        string $volume = '',
        array $env = []
    ): void {
        $command = 'run -d --name ' . $name;
        $command .= $this->buildPortString($ports);
        if (!empty($volume)) {
            $command .= ' -v "' . $volume . '"';
        }
        $command .= $this->buildEnvironmentString($env);
        $command .= ' ' . $image;

        $this->executeCommand($command);
    }

    /**
     * @param string $name
     * @throws \Exception
     */
    public function stop(string $name): void
    {
        $this->executeCommand('stop ' . $name);
    }

    /**
     * @param string $name
     * @throws \Exception
     */
    public function remove(string $name): void
    {
        $this->executeCommand('rm ' . $name);
    }

    /**
     * @param string $image
     * @throws \Exception
     */
    public function installImage(string $image)
    {
        $this->executePassthruCommand('pull ' . $image);
    }

    /**
     * @param string $dockerfilePath
     * @param string $tag
     * @throws \Exception
     */
    public function build(string $dockerfilePath, string $tag)
    {
        $this->executePassthruCommand('build ' . $dockerfilePath . ' -t ' . $tag);
    }

    /**
     * @param string $command
     * @throws \Exception
     */
    private function executePassthruCommand(string $command): void
    {
        $this->isRunning();
        $this->cli->passthru('docker ' . $command);
    }

    /**
     * @param string $command
     * @throws \Exception
     */
    private function executeCommand(string $command): void
    {
        $this->isRunning();
        $this->cli->run('docker ' . $command, function ($code, $output) {
            if (strpos($output, 'No such container') !== false) {
                return;
            }
            throw new \Exception($output);
        });
    }

    /**
     * @throws \Exception
     */
    private function isRunning(): void
    {
        $this->cli->run('docker', function ($code, $output) {
            throw new \Exception($output);
        });
    }

    private function buildPortString(array $ports): string
    {
        $output = '';
        foreach ($ports as $port) {
            $output .= ' -p "' . $port . '"';
        }

        return $output;
    }

    private function buildEnvironmentString(array $envs): string
    {
        $output = '';
        foreach ($envs as $env) {
            $output .= ' -e "' . $env . '"';
        }

        return $output;
    }
}
