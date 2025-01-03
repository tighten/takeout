<?php

namespace App;

trait WritesToConsole
{
    public function alert(string $message): void
    {
        app('console')->alertPrompt($message);
    }

    public function warn(string $message): void
    {
        app('console')->warnPrompt($message);
    }

    public function error(string $message): void
    {
        app('console')->errorPrompt($message);
    }

    public function line(string $message): void
    {
        app('console')->linePrompt($message);
    }

    public function info(string $message): void
    {
        app('console')->infoPrompt($message);
    }

    public function task(string $message, $callable): void
    {
        app('console')->taskPrompt($message, $callable);
    }

    public function ask(string $message, $default = null, $validate = null)
    {
        return app('console')->askPromptQuestion($message, $default, $validate);
    }
}
