<?php

namespace App\Shell;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\error;
use function Laravel\Prompts\note;

class Shell
{
    protected $output;

    public function __construct(ConsoleOutput $output)
    {
        $this->output = $output;
    }

    public function exec(string $command, array $parameters = [], bool $quiet = false, bool $plain = false): Process
    {
        $process = $this->buildProcess($command);
        $process->run(function ($type, $buffer) use ($quiet, $plain) {
            if (empty($buffer) || $buffer === PHP_EOL || $quiet) {
                return;
            }

            if ($plain) {
                note($buffer);
                return;
            }

            if ($type === Process::ERR) {
                error('Something went wrong.');
                note(' <bg=red;fg=white> ERR </> ' . $this->formatMessage($buffer));
            } else {
                note(' <bg=green;fg=white> OUT </> ' . $this->formatMessage($buffer));
            }
        }, $parameters);

        return $process;
    }

    public function buildProcess(string $command): Process
    {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null);

        return $process;
    }

    public function execQuietly(string $command, array $parameters = []): Process
    {
        return $this->exec($command, $parameters, $quiet = true);
    }

    private function formatMessage(string $buffer): string
    {
        return rtrim(collect(explode("\n", trim($buffer)))->reduce(function ($carry, $line) {
            return $carry .= trim($line) . "\n";
        }, ''));
    }
}
