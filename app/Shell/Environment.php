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

    private function isLinuxOs()
    {
        return PHP_OS_FAMILY === 'Linux';
    }

    public function portIsAvailable($port): bool
    {
        $isLinux = $this->isLinuxOs();
        $netstatCmd = 'netstat';
        $portText = "\.{$port}\s"; // mac default port behavior 127.0.0.1.3306 for mysql

        // if its linux we need to check WSL also,
        // because WSL runs docker from Windows host
        // and it uses netstat.exe from Windows
        // if we don't use this, WSL won't find ports conflict
        if ($isLinux) {
            $portText = "\:{$port}\s"; // win/linux default port behavior 127.0.0.1:3306 for mysql

            $linuxVersion = $this->shell->execQuietly('cat /proc/version');
            $isWSL = Str::contains($linuxVersion->getOutput(), 'microsoft');

            if ($isWSL) {
                $netstatCmd = 'netstat.exe';
            }
        }

        // Check to see if the system is running a service with the desired port
        $process = $this->shell->execQuietly("{$netstatCmd} -vanp tcp | grep '{$portText}' | grep -v 'TIME_WAIT' | grep -v 'CLOSE_WAIT' | grep -v 'FIN_WAIT'");

        // A successful netstat command means a port in use was found
        return ! $process->isSuccessful();
    }
}
