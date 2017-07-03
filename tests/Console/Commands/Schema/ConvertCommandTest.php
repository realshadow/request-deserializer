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

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('request_deserializer.request', [
            'schema_path'   => dirname(__DIR__),
            'publish_path' => '',
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Provided JSON schema does not exist.
     */
    public function testShouldFailIfSchemaDoesNotExist()
    {
        vfsStream::setup('root', null);

        $this->artisan('schema:convert', [
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

        $this->artisan('schema:convert', [
            'schema' => '../../Fixtures/schema/convert/no_properties.json',
            'request' => vfsStream::url('root'),
            '--no-interaction' => true,
        ]);
    }

    public function testShouldConvertSchemaToRequest()
    {
        $filesystem = app('files');

        vfsStream::setup('root', null);

        $target = 'root/CreateRequest.php';

        $this->app['config']->set('request_deserializer.request', [
            'schema_path'   => dirname(__DIR__),
            'publish_path' => vfsStream::url('root'),
            'namespace' => 'App\Requests'
        ]);

        $this->artisan('schema:convert', [
            'schema' => '../../Fixtures/schema/convert/schema.json',
            'request' => 'CreateRequest.php',
            '--no-interaction' => true,
        ]);

        $this->assertEquals(
            $filesystem->get(__DIR__ . '/../../../Fixtures/schema/convert/request.php'),
            $filesystem->get(vfsStream::url($target))
        );
    }

}
