<?php

namespace Realshadow\RequestDeserializer\Testing\Console\Commands\Schema;

use Illuminate\Contracts\Console\Kernel;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Command\Command;


/**
 * Tests for schema:convert command
 *
 * @package Realshadow\RequestDeserializer\Testing\Console\Commands\Schema
 * @author Lukáš Homza <lukashomz@gmail.com>
 */
class ConvertCommandTest extends \Orchestra\Testbench\TestCase
{

    protected function getPackageProviders($app)
    {
        return ['Realshadow\RequestDeserializer\Providers\LaravelServiceProvider'];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Provided JSON schema does not exist.
     */
    public function testShouldFailIfSchemaDoesNotExist()
    {
        vfsStream::setup('root', null);

        \Artisan::call('schema:convert', [
            'schema' => 'foo/bar/baz',
            'request' => vfsStream::url('root'),
            '--no-interaction' => true,
        ]);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Provided JSON schema does not have properties.
     */
    public function testShouldFailIfSchemaDoesNotContainProperties()
    {
        vfsStream::setup('root', null);

        \Artisan::call('schema:convert', [
            'schema' => 'tests/Fixtures/schema/convert/no_properties.json',
            'request' => vfsStream::url('root'),
            '--no-interaction' => true,
        ]);
    }

    public function testShouldConvertSchemaToRequest()
    {
        $filesystem = app('files');

        $command = \Mockery::mock(
            '\Realshadow\RequestDeserializer\Console\Commands\Schema\ConvertCommand[anticipate]',
            [$this->app->make('files')]
        );

        $command->shouldReceive('anticipate')
            ->once()
            ->andReturn('Realshadow\RequestDeserializer\Testing\Fixtures\Schema');

        $this->app->make(Kernel::class)
            ->registerCommand($command);

        vfsStream::setup('root', null);

        $target = 'root/CreateRequest.php';

        \Artisan::call('schema:convert', [
            'schema' => 'tests/Fixtures/schema/convert/schema.json',
            'request' => vfsStream::url($target),
            '--no-interaction' => true,
        ]);

        $this->assertEquals(
            $filesystem->get(__DIR__ . '/../../../Fixtures/schema/convert/request.php'),
            $filesystem->get(vfsStream::url($target))
        );
    }

}
