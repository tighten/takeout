<?php

namespace App\Shell;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;

class Shell
{
    protected $output;

    public function __construct(ConsoleOutput $output)
    {
        $this->output = $output;
    }

    public function exec(string $command, array $parameters = [], bool $quiet = false): Process
    {
        $process = $this->buildProcess($command);
        $process->run(function ($type, $buffer) use ($quiet) {
            if (empty($buffer) || $buffer === PHP_EOL || $quiet) {
                return;
            }

            if ($type === Process::ERR) {
                return $this->output->writeLn($this->formatErrorMessage($buffer));
            }

            $this->output->writeLn($this->formatMessage($buffer));
        }, $parameters);

        return $process;
    }

    public function execQuietly(string $command, array $parameters = []): Process
    {
        return $this->exec($command, $parameters, $quiet = true);
    }

    public function formatStartMessage(string $buffer): string
    {
        return rtrim(sprintf('<bg=blue;fg=white> RUN </> <fg=blue>%s</>', $buffer));
    }

    public function formatErrorMessage(string $buffer): string
    {
        return rtrim(sprintf('<bg=red;fg=white> ERR </> %s', $buffer));
    }

    public function formatMessage(string $buffer): string
    {
        return rtrim(sprintf('<bg=green;fg=white> OUT </> %s', $buffer));
    }

    public function buildProcess(string $command): Process
    {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null);

        return $process;
    }
}
