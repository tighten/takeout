<?php

namespace Tests\Feature;

use Tests\TestCase;

class ListServicesTest extends TestCase
{
    /** @test */
    function it_converts_command_output_to_a_table()
    {
        $this->markTestIncomplete('Write this');
        // @todo Mock the Docker::containers()->getOutput() to return a fixed string
        \Artisan::call('list:services');
        // @todo Ensure that containersTable() method contains the correctly-structued
        //       rows: headers row, then one each for each entry
    }
}
