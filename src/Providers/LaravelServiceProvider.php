<?php

namespace Realshadow\RequestDeserializer\Providers;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\SerializerBuilder;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use Realshadow\RequestDeserializer\Console\Commands\Schema\ConvertCommand;
use Realshadow\RequestDeserializer\JsonSchema\Uri\Retrievers\FileGetContents;
use Realshadow\RequestDeserializer\Http\Request\RequestDeserializationHandler;
use Realshadow\RequestDeserializer\Http\Request\RequestHandler;
use Realshadow\RequestDeserializer\Serializers\LaravelSerializer;


/**
 * Service provider for API wrapper responsible for loading all neccessary components
 *
 * @package Realshadow\RequestDeserializer\Providers
 * @author Lukáš Homza <lukashomz@gmail.com>
 */
class LaravelServiceProvider extends ServiceProvider
{

    const CONFIG_KEY = 'request_deserializer';

    /**
     * Initializes serializer with required laravel bindings
     *
     * @return SerializerBuilder
     */
    protected function prepareSerializer()
    {
        return (new LaravelSerializer())->getBuilderInstance();
    }

    /**
     * Register third party providers
     */
    protected function registerProviders()
    {
        //region Purifier
        $this->app->register(\Mews\Purifier\PurifierServiceProvider::class);

        $this->app->bind('purifier', \Realshadow\RequestDeserializer\Validation\Purifier::class);
        //endregion
    }

    /**
     * Registers/binds all third party classes to service container and/or autoloader
     *
     * @throws \InvalidArgumentException
     */
    protected function registerClasses()
    {
        //region Annotations
        AnnotationRegistry::registerLoader('class_exists');

        $this->app->singleton('app.annotation_reader', function (Application $app) {
            return new AnnotationReader;
        });
        //endregion

        //region Serializer
        $this->app->singleton('api.serializer', function (Application $app) {
            return $this->prepareSerializer()->build();
        });
        //endregion

        //region JSON Schema Constraint
        $this->app->singleton('api.schema.constraint', function (Application $app) {
            $fileRetriever = new FileGetContents;
            $fileRetriever->setBasePath(config(self::CONFIG_KEY . '.request.schema_path') . DIRECTORY_SEPARATOR);

            $retriever = (new UriRetriever)
                ->setUriRetriever($fileRetriever);

            $constraintFactory = new Factory(new SchemaStorage($retriever));
            $constraintFactory->setConfig(Constraint::CHECK_MODE_APPLY_DEFAULTS | Constraint::CHECK_MODE_COERCE_TYPES);

            return $constraintFactory;
        });
        //endregion

        //region JSON Schema Validator
        $this->app->singleton('api.schema.validator', function (Application $app) {
            return new Validator(
                $app->make('api.schema.constraint')
            );
        });
        //endregion

        //region RequestHandler
        $this->app->singleton(RequestHandler::class, function (Application $app) {
            $serializer = $this->prepareSerializer()
                ->configureListeners(function (EventDispatcher $dispatcher) use ($app) {
                    $dispatcher->addSubscriber(
                        new RequestDeserializationHandler(
                            $app->make('api.schema.validator'),
                            $app->make('purifier')
                        )
                    );
                })
                ->build();

            return new RequestHandler($serializer);
        });
        //endregion
    }

    /**
     * Registers all provided console commands
     */
    protected function registerCommands()
    {
        $this->commands([
            ConvertCommand::class,
        ]);
    }

    /**
     * Perform post-registration booting of services
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }

        $this->publishes([__DIR__ . '/../../config/purifier.php' => config_path('purifier.php')]);

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/request_deserializer.php',
            'request_deserializer'
        );
    }

    /**
     * Register every component
     *
     * @throws \InvalidArgumentException
     */
    public function register()
    {
        $this->registerProviders();

        $this->registerClasses();
    }

}
