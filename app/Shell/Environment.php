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

    public function portIsAvailable($port): bool
    {
        // To check if the socket is available, we'll attempt to open a socket on the port.
        // If we cannot open the socket, it means there's nothing running on it, so the
        // port is available. If we are successful, that means it is already in use.

        $socket = @fsockopen('localhost', $port, $errorCode, $errorMessage, timeout: 5);

        if (! $socket) {
            return true;
        }

        fclose($socket);

        return false;
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
}
