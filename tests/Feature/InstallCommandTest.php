<?php

namespace Tests\Feature;

use App\Exceptions\InvalidServiceShortnameException;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class InstallCommandTest extends TestCase
{
    /** @test */
    function it_finds_services_by_shortname()
    {
        $this->markTestIncomplete();

        // Hopefully keep commands from being run on my computer
        $this->mock(Shell::class, function ($mock) {
            $mock->shouldIgnoreMissing();
        });

        $this->artisan('install meilisearch');
            // ->expectsQuestion('What is your name?', 'Taylor Otwell')
            // ->expectsQuestion('Which language do you program in?', 'PHP')
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
