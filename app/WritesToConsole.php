<?php

namespace App;

trait WritesToConsole
{
    public function alert(string $message): void
    {
        app('console')->alert($message);
    }

    public function warn(string $message): void
    {
        app('console')->warn($message);
    }

    public function error(string $message): void
    {
        app('console')->error($message);
    }

    public function line(string $message): void
    {
        app('console')->line($message);
    }

    public function info(string $message): void
    {
        app('console')->info($message);
    }

    public function task(string $message, $callable): void
    {
        app('console')->task($message, $callable);
    }
}
