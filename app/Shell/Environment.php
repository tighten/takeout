<?php

namespace App\Shell;

class Environment
{
    protected $shell;

    public function __construct(Shell $shell)
    {
        $this->shell = $shell;
    }

    public function isMacOs(): bool
    {
        return PHP_OS_FAMILY === 'Darwin';
    }

    public function isLinuxOs(): bool
    {
        return PHP_OS_FAMILY === 'Linux';
    }

    public function isWindowsOs(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }

    public function portIsAvailable(int $port): bool
    {
        // To check if the port is available, we'll attempt to open a socket connection to it.
        // Note that the logic here is flipped: successfully openning the socket connection
        // means something is using it. If it fails to open, that port is likely unused.
        $socket = @fsockopen($this->localhost(), $port, $errorCode, $errorMessage, timeout: 5);

        if (! $socket) {
            return true;
        }

        fclose($socket);

        return false;
    }

    public function isTakeoutRunningInsideDocker(): bool
    {
        return boolval($_SERVER['TAKEOUT_CONTAINER'] ?? false);
    }

    public function userIsInDockerGroup(): bool
    {
        return $this->shell->execQuietly('groups | grep docker')->isSuccessful();
    }

    public function homeDirectory(): string
    {
        $home = rtrim(getenv('HOME'), '/');

        if (! empty($home)) {
            return $home;
        }

        // Windows
        if (! empty($_SERVER['HOMEDRIVE']) && ! empty($_SERVER['HOMEPATH'])) {
            return rtrim($_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'], '\\/');
        }

        return '~';
    }

    private function localhost(): string
    {
        return $this->isTakeoutRunningInsideDocker() ? 'host.docker.internal' : 'localhost';
    }
}
