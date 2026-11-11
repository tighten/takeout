<?php

namespace Tests\Feature;

use App\Services;
use App\Services\Category;
use App\Services\RustFS;
use App\Shell\Docker;
use App\Shell\DockerTags;
use LaravelZero\Framework\Commands\Command;
use Mockery as M;
use Tests\TestCase;

class RustFSTest extends TestCase
{
    /** @test */
    public function it_registers_rustfs_as_a_storage_service()
    {
        $this->assertSame(RustFS::class, app(Services::class)->get('rustfs'));
        $this->assertSame(Category::STORAGE, RustFS::category());
        $this->assertSame('RustFS', RustFS::name());
    }

    /** @test */
    public function it_enables_rustfs_with_defaults()
    {
        app()->instance('console', M::mock(Command::class)->shouldIgnoreMissing());
        $tags = M::mock(DockerTags::class);
        $tags->shouldReceive('resolveTag')->with('latest')->andReturn('latest');
        app()->bind(DockerTags::class, fn () => $tags);

        $this->expectContainer([
            'organization' => 'rustfs', 'image_name' => 'rustfs', 'port' => 9000,
            'tag' => 'latest', 'volume' => 'rustfs_data', 'console_port' => 9001,
            'access_key' => 'rustfsadmin', 'secret_key' => 'rustfsadmin',
            'container_name' => 'TO--rustfs--latest--9000--9001', 'alias' => 'rustfs-latest',
        ]);

        app(RustFS::class)->enable(true);
    }

    /** @test */
    public function it_enables_rustfs_with_custom_settings()
    {
        app()->instance('console', M::mock(Command::class, function ($mock) {
            $mock->shouldReceive('askPromptQuestion')->with('Which host port would you like rustfs to use?', 9000, M::any())->andReturn(9100);
            $mock->shouldReceive('askPromptQuestion')->with('Which tag (version) of rustfs would you like to use?', 'latest', M::any())->andReturn('1.0.0');
            $mock->shouldReceive('askPromptQuestion')->with('What is the Docker volume name?', 'rustfs_data', M::any())->andReturn('custom_data');
            $mock->shouldReceive('askPromptQuestion')->with('Which host port would you like RustFS Console to use?', 9001, M::any())->andReturn(9101);
            $mock->shouldReceive('askPromptQuestion')->with('What will the RustFS access key be?', 'rustfsadmin', M::any())->andReturn('customaccess');
            $mock->shouldReceive('askPromptQuestion')->with('What will the RustFS secret key be?', 'rustfsadmin', M::any())->andReturn('customsecret');
            $mock->shouldIgnoreMissing();
        }));
        $tags = M::mock(DockerTags::class);
        $tags->shouldReceive('resolveTag')->with('1.0.0')->andReturn('1.0.0');
        app()->bind(DockerTags::class, fn () => $tags);

        $this->expectContainer([
            'organization' => 'rustfs', 'image_name' => 'rustfs', 'port' => 9100,
            'tag' => '1.0.0', 'volume' => 'custom_data', 'console_port' => 9101,
            'access_key' => 'customaccess', 'secret_key' => 'customsecret',
            'container_name' => 'TO--rustfs--1.0.0--9100--9101', 'alias' => 'rustfs1.0',
        ]);

        app(RustFS::class)->enable();
    }

    private function expectContainer(array $parameters): void
    {
        $template = '-p "${:port}":9000 \
        -p "${:console_port}":9001 \
        -e RUSTFS_ACCESS_KEY="${:access_key}" \
        -e RUSTFS_SECRET_KEY="${:secret_key}" \
        -e RUSTFS_ADDRESS=":9000" \
        -e RUSTFS_CONSOLE_ADDRESS=":9001" \
        -e RUSTFS_CONSOLE_ENABLE=true \
        -v "${:volume}":/data \
        "${:organization}"/"${:image_name}":"${:tag}" /data';

        $this->mock(Docker::class, function ($mock) use ($parameters, $template) {
            $mock->shouldReceive('imageIsDownloaded')->andReturn(true);
            $mock->shouldReceive('bootContainer')->with(app(RustFS::class)->sanitizeDockerRunTemplate($template), $parameters)->once();
        });
    }
}
