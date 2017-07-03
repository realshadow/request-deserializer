<?php

namespace Realshadow\RequestDeserializer\JsonSchema\Uri\Retrievers;

use JsonSchema\Uri\Retrievers\FileGetContents as OriginalFileGetContents;
use JsonSchema\Uri\Retrievers\UriRetrieverInterface;


/**
 * Override of the originle retriever so we can look for nested and/or inherited schemas on disk in specified base path
 *
 * @package Realshadow\RequestDeserializer\JsonSchema\Uri\Retrievers
 * @author Lukáš Homza <lukashomz@gmail.com>
 */
class FileGetContents extends OriginalFileGetContents implements UriRetrieverInterface
{

    /**
     * @var string $basePath
     */
    private $basePath;

    /**
     * @param string $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * {@inheritDoc}
     * @see \JsonSchema\Uri\Retrievers\UriRetrieverInterface::retrieve()
     */
    public function retrieve($uri)
    {
        $scheme = parse_url($uri)['scheme'] . '://';

        $uri = str_replace($scheme, $scheme . $this->basePath, $uri);

        return parent::retrieve($uri);
    }

}
