<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class HelpCommand extends Command
{
    /**
     * The signature of the command.
     */
    protected $signature = 'helpcommand';

    /**
     * The description of the command.
     */
    protected $description = 'Display a help menu.';

    protected $indent = 22;
    protected $commands = [
        'install' => 'Install a service from a list of options',
        'install {service}' => 'Install the provided service',
        'uninstall' => 'Uninstall a service from a list of options',
        'uninstall {service}' => 'Uninstall the provided service',
        'list:services' => 'List all installed services',
    ];

    public function handle(): void
    {
        $this->line("\n  <fg=white;options=bold>Takeout</>  <fg=green;options=bold>" . config('app.version') . "</>");
        $this->line("\n  <comment>Usage:</comment>");
        $this->line("    takeout <command> [arguments]\n");
        $this->line("  <comment>Commands:</comment>");

        foreach ($this->commands as $command => $description) {
            $spaces = $this->makeSpaces(strlen($command));
            $this->line("    <info>{$command}</info>{$spaces}{$description}");
        }
    }

    public function makeSpaces($count)
    {
        return str_repeat(" ", $this->indent - $count);
    }
}
