<?php

namespace Realshadow\RequestDeserializer\Testing\Serializers;

use Illuminate\Support\Collection;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Realshadow\RequestDeserializer\Serializers\LaravelSerializer;
use Realshadow\RequestDeserializer\Testing\Fixtures\CollectionTest;


/**
 * Tests for laravel serializer
 *
 * @package Realshadow\RequestDeserializer\Testing\Serializers
 * @author Lukáš Homza <lukashomz@gmail.com>
 */
class LaravelSerializerTest extends \Orchestra\Testbench\TestCase
{

    protected function getPackageProviders($app)
    {
        return ['Realshadow\RequestDeserializer\Providers\LaravelServiceProvider'];
    }

    public function testShouldGetBuilderInstance()
    {
        $serializer = new LaravelSerializer;

        $this->assertInstanceOf(SerializerBuilder::class, $serializer->getBuilderInstance());
    }

    public function testShouldGetSerializerInstance()
    {
        $serializer = new LaravelSerializer;

        $this->assertInstanceOf(Serializer::class, $serializer->make());
    }

    public function testShouldSerializeCollection()
    {
        $serializer = new LaravelSerializer;

        $items = ['foo', 'bar', 'baz'];

        $expected = json_encode($items);

        $actual = $serializer->make()->serialize(new Collection($items), 'json');

        $this->assertEquals($expected, $actual);
    }

    public function testShouldDeserializeToCollection()
    {
        $serializer = new LaravelSerializer;

        $items = ['items' => ['foo', 'bar', 'baz']];

        $result = $serializer->make()->deserialize(json_encode($items), CollectionTest::class, 'json');

        $this->assertInstanceOf(Collection::class, $result->getItems());
    }

}
