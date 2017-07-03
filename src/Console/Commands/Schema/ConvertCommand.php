<?php

namespace Realshadow\RequestDeserializer\Console\Commands\Schema;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;


/**
 * Command for generating request entity from provided JSON schema definition
 *
 * @package Realshadow\RequestDeserializer\Console\Commands\Schema
 * @author Lukáš Homza <lukashomz@gmail.com>
 */
class ConvertCommand extends AbstractSchemaCommand
{

    /**
     * Filesystem handler
     *
     * @var Filesystem $filesystem
     */
    private $filesystem;

    /**
     * Path to request entity templates
     *
     * @var string $templatePath
     */
    private $templatePath;

    /**
     * Default path to folder with schemas
     *
     * @var string $schemaPath
     */
    private $schemaPath;

    /**
     * Default path to folder where generated requests will be stored
     *
     * @var string $publishPath
     */
    private $publishPath;

    /**
     * Default namespace
     *
     * @var string $namespace
     */
    private $namespace;

    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'schema:convert';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Converts JSON schema to request entity';

    /**
     * Maps JSON schema type to PHP internal type
     *
     * @param string[]|string $type
     *
     * @return string
     */
    private function toPhpInternalType($type)
    {
        if (is_array($type)) {
            $type = end($type);
        }

        switch ($type) {
            case 'integer':
                $type = 'int';

                break;
            case 'boolean':
                $type = 'bool';

                break;
            case 'double':
                $type = 'float';

                break;
        }

        return $type;
    }

    /**
     * Maps PHP internal type to JMS type
     *
     * @param string[]|string $type
     *
     * @return string
     */
    private function toJmsType($type)
    {
        if (is_array($type)) {
            $type = end($type);
        }

        switch ($type) {
            case 'float':
                $type = 'double';

                break;
        }

        return $type;
    }

    /**
     * Handles all properties specified in JSON schema
     *
     * @param array $schema
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function prepareProperties(array $schema)
    {
        $template = $this->filesystem->get($this->templatePath . DIRECTORY_SEPARATOR . 'property.tpl');

        $total = count($schema['properties']);

        $i = 1;
        $output = '';
        foreach ($schema['properties'] as $property => $data) {
            $replace = [
                'schemaProperty' => $property,
                'property' => camel_case($property),
                'version' => '1.x',
                'phpType' => $this->toPhpInternalType($data['type']),
                'jmsType' => $this->toJmsType($data['type']),
            ];

            $output .= $this->replace($replace, $template . str_repeat(PHP_EOL, $i !== $total ? 2 : 0));

            $i++;
        }

        return $output;
    }

    /**
     * Handles all getter methods for properties specified in JSON schema
     *
     * @param array $schema
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function prepareMethods(array $schema)
    {
        $template = $this->filesystem->get($this->templatePath . DIRECTORY_SEPARATOR . 'method.tpl');

        $total = count($schema['properties']);

        $i = 1;
        $output = '';
        foreach ($schema['properties'] as $property => $data) {
            $isNullable = true;
            if (isset($schema['required']) && in_array($property, $schema['required'], true)) {
                $isNullable = false;
            }

            $type = $this->toPhpInternalType($data['type']);

            $replace = [
                'property' => camel_case($property),
                'method' => studly_case($property),
                'phpType' => ($isNullable ? 'null|' : '') . $type,
                'returnType' => ($isNullable ? '?' : '') . $type,
            ];

            $output .= $this->replace($replace, $template . str_repeat(PHP_EOL, $i !== $total ? 2 : 0));

            $i++;
        }

        return $output;
    }

    /**
     * Replaces data in templates to actual code
     *
     * @param array $what
     * @param string $template
     *
     * @return string
     */
    private function replace(array $what, $template)
    {
        foreach ($what as $property => $value) {
            $template = str_replace(static::PATTERN_PREFIX . $property . static::PATTERN_SUFFIX, $value, $template);
        }

        return $template;
    }

    /**
     * @inheritdoc
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;

        $this->templatePath = $this->path(__DIR__, '..', '..', '..', '..', 'resources', 'templates', 'request');
        $this->schemaPath = $this->config('request.schema_path');
        $this->publishPath = $this->config('request.publish_path');
        $this->namespace = $this->config('request.namespace');

        $this->addArgument('schema', InputArgument::REQUIRED, 'Path JSON schema');
        $this->addArgument('request', InputArgument::REQUIRED, 'Path to resulting request object');
    }

    /**
     * Converts JSON schema to request entity
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $schemaPath = $this->path($this->schemaPath, $this->argument('schema'));

        $requestPath = $this->path($this->publishPath, $this->argument('request'));
        $className = pathinfo($requestPath, PATHINFO_FILENAME);

        if ( ! $this->filesystem->exists($schemaPath)) {
            throw new \InvalidArgumentException('Provided JSON schema does not exist.');
        }

        $schema = json_decode($this->filesystem->get($schemaPath), true);
        if ( ! isset($schema['properties'])) {
            throw new \UnexpectedValueException('Provided JSON schema does not have properties.');
        }

        $this->info('Preparing new <options=bold>' . $className . '</> class');

        $template = $this->filesystem->get(
            $this->path($this->templatePath, 'class.tpl')
        );

        $data = [
            'className' => $className,
            'namespace' => $this->namespace,
            'schemaPath' => $schemaPath,
            'properties' => $this->prepareProperties($schema),
            'methods' => $this->prepareMethods($schema),
        ];

        if ( ! $this->filesystem->exists($this->publishPath)) {
            $this->filesystem->makeDirectory($this->publishPath, 0755, true);
        }

        $this->filesystem->put(
            $requestPath,
            $this->replace($data, $template)
        );
    }

}
