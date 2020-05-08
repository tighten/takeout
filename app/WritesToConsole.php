<?php

namespace App;

trait WritesToConsole
{
    public function alert(string $message)
    {
        app('console')->alert($message);
    }

    public function warn(string $message)
    {
        app('console')->warn($message);
    }

    public function error(string $message)
    {
        app('console')->error($message);
    }

    public function line(string $message)
    {
        app('console')->line($message);
    }

    public function info(string $message)
    {
        app('console')->info($message);
    }
}
