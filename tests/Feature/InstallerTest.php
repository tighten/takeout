<?php

namespace Tests\Feature;

use Tests\TestCase;

class InstallerTest extends TestCase
{
    /** @test */
    function it_can_be_exited_without_errors()
    {
        $this->markTestIncomplete('Need to ask Nuno if we can test laravel-console-menu');
        \Artisan::call('install');
        // @todo: Choose the exit option from the UI
        // @todo: ask nuno if this is possible
        // @todo: Assert ... true is true?? ... to make sure we don't get any exceptions
    }
}
