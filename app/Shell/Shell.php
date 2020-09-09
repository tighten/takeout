<?php

namespace App\Shell;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;

class Shell
{
    const SPACES = 6; // Same spacing as " OUT " plus a space before output

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

            if ($this->isMultiline($buffer)){
                $buffer = $this->formatMultiline($buffer);
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

    protected function formatMultiline($buffer): string
    {
        $bufferArray = explode(PHP_EOL, $buffer);
        $buffer = $this->alignMultiline($bufferArray);
        return implode(PHP_EOL, $buffer);
    }

    protected function isMultiline($buffer): bool
    {
        return substr_count($buffer, "\n") > 1;
    }

    protected function alignMultiline(array $bufferArray): array
    {
        return collect($bufferArray)->map(function ($item, $index) {
            return $index > 0 ? Str::of($item)->prepend(str_repeat(' ', self::SPACES)) : $item;
        })->toArray();
    }
}
