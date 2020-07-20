<?php

namespace Tests\Feature;

use App\Exceptions\InvalidServiceShortnameException;
use Tests\TestCase;

class InstallCommandTest extends TestCase
{
    /** @test */
    function it_finds_services_by_shortname()
    {
        // Hopefully keep commands from being run on my computer
        $this->mock(Shell::class, function ($mock) {
            $mock->shouldIgnoreMissing();
        });

        $this->artisan('install meilisearch')
            ->expectsQuestion('Which host port would you like this service to use?', '1234')
            ->expectsQuestion('Which tag (version) of this service would you like to use?', 'v1.1')
            ->expectsQuestion('What is the Docker volume name?', 'super_volume');

            // @Todo fix this so it's not actually trying to find that version...

            // ->expectsOutput('Your name is Taylor Otwell and you program in PHP.')
            // ->assertExitCode(0);
        // @todo: assert that the Meilisearch service was matched


    }

    /** @test */
    function it_displays_error_if_invalid_shortname_passed()
    {
        $this->expectException(InvalidServiceShortnameException::class);
        $this->artisan('install asdfasdfadsfasdfadsf')
            ->assertExitCode(0);
    }
}
