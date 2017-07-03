<?php

namespace Realshadow\RequestDeserializer\Serializers;

use Illuminate\Support\Collection;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\VisitorInterface;


/**
 * Extension of the base serializer which adds capabilities to work with Laravel's Collection
 *
 * @package Realshadow\RequestDeserializer\Serializers
 * @author Lukáš Homza <lukashomz@gmail.com>
 */
class LaravelSerializer extends AbstractSerializer
{

    /**
     * Adds the Collection handlers to handler stack
     *
     * @return \Closure
     */
    protected function addCollectionHandler()
    {
        return function (HandlerRegistry $registry) {
            $registry->registerHandler(
                GraphNavigator::DIRECTION_SERIALIZATION,
                Collection::class,
                'json',
                function (VisitorInterface $visitor, Collection $collection, array $type, Context $context) {
                    return $visitor->visitArray($collection->values(), $type, $context);
                }
            );

            $registry->registerHandler(
                GraphNavigator::DIRECTION_DESERIALIZATION,
                Collection::class,
                'json',
                function (VisitorInterface $visitor, array $data, array $type, Context $context) {
                    $data = $visitor->visitArray($data, $type, $context);

                    return new Collection($data);
                }
            );
        };
    }

    /**
     * LaravelSerializer constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->builder->configureHandlers($this->addCollectionHandler());
    }

}
