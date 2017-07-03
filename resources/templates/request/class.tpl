<?php

namespace {namespace};

use Realshadow\RequestDeserializer\Contracts\RequestInterface;
use JMS\Serializer\Annotation as JMS;


/**
 * Request definition of {className}
 *
 * @package {namespace}
 */
class {className} implements RequestInterface
{

{properties}

    /**
     * @inheritdoc
     */
    public static function shouldValidate()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function getVersionConstraint()
    {
        return '1.0';
    }

    /**
     * @inheritdoc
     */
    public function getSchema()
    {
        return '{schemaPath}';
    }

{methods}

}
