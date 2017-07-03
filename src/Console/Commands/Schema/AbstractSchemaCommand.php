<?php

namespace Realshadow\RequestDeserializer\Console\Commands\Schema;

use Illuminate\Console\Command;
use Realshadow\RequestDeserializer\Providers\LaravelServiceProvider;


/**
 * Base class for all schema commands
 *
 * @package Realshadow\RequestDeserializer\Console\Commands\Schema
 * @author Lukáš Homza <lukashomz@gmail.com>
 */
abstract class AbstractSchemaCommand extends Command
{

    /**
     * Prefix used by pattern used for replacing data in template files
     */
    const PATTERN_PREFIX = '{';

    /**
     * Suffix used by pattern used for replacing data in template files
     */
    const PATTERN_SUFFIX = '}';

    /**
     * File extension used by template files
     */
    const TEMPLATE_EXTENSION = 'tpl';

    /**
     * Get the value from configuration depending on provided configuration path
     *
     * @param string $path
     * @param null $default
     *
     * @return null|string
     */
    protected function config($path, $default = null)
    {
        return config(LaravelServiceProvider::CONFIG_KEY . '.' . $path, $default);
    }

    /**
     * Merges provided chunks into path
     *
     * @param array ...$chunks
     *
     * @return string
     */
    protected function path(...$chunks)
    {
        return join(DIRECTORY_SEPARATOR, $chunks);
    }

}
