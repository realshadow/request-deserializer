<?php

namespace Realshadow\RequestDeserializer\Serializers;

use Illuminate\Config\Repository;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Realshadow\RequestDeserializer\Providers\LaravelServiceProvider;


/**
 * Class AbstractSerializer
 *
 * @package Realshadow\RequestDeserializer\Serializers
 * @author Lukáš Homza <lukashomz@gmail.com>
 */
class AbstractSerializer
{

    /**
     * JMS serializer builder
     *
     * @var SerializerBuilder $builder
     */
    protected $builder;

    /**
     * AbstractSerializer constructor
     *
     * Builder is initialized with debug and cache setup depending on provided configuration
     * and with default listeners and handlers
     */
    public function __construct()
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = app(Repository::class);

        $this->builder = SerializerBuilder::create()
            ->setDebug($config->get('app.debug'))
            ->addDefaultHandlers()
            ->addDefaultListeners();

        $cache = $config->get(LaravelServiceProvider::CONFIG_KEY . '.serializer.cache');
        if ($cache) {
            $this->builder->setCacheDir($cache);
        }
    }

    /**
     * Gets the builder instance
     *
     * @return SerializerBuilder
     */
    public function getBuilderInstance()
    {
        return $this->builder;
    }

    /**
     * Builds the serializer
     *
     * @return Serializer
     */
    public function make()
    {
        return $this->builder->build();
    }

}
