<?php

namespace App\Shell;

use Illuminate\Support\Str;

class Environment
{
    protected $shell;

    public function __construct(Shell $shell)
    {
        $this->shell = $shell;
    }

    public function isLinuxOs(): bool
    {
        return PHP_OS_FAMILY === 'Linux';
    }

    public function isWindowsOs(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }

    public function portIsAvailable($port): bool
    {
        // E.g. Win/Linux: 127.0.0.1:3306 , macOS: 127.0.0.1.3306
        $portText = $this->isLinuxOs() ? "\:{$port}\s" : "\.{$port}\s";

        $netstatCmd = $this->netstatCmd();

        // Check to see if the system is running a service with the desired port
        $process = $this->shell->execQuietly("{$netstatCmd} -vanp tcp | grep '{$portText}' | grep -v 'TIME_WAIT' | grep -v 'CLOSE_WAIT' | grep -v 'FIN_WAIT'");

        // A successful netstat command means a port in use was found
        return ! $process->isSuccessful();
    }

    public function netstatCmd(): string
    {
        $netstatCmd = 'netstat';

        if ($this->isLinuxOs()) {
            $linuxVersion = $this->shell->execQuietly('cat /proc/version');
            $isWSL = Str::contains($linuxVersion->getOutput(), 'microsoft');

            if ($isWSL) {
                $netstatCmd = 'netstat.exe';
            }
        }

        return $netstatCmd;
    }
}
