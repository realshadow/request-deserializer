<?php

namespace Realshadow\RequestDeserializer\Testing\Fixtures\Schema;

use Realshadow\RequestDeserializer\Contracts\RequestInterface;
use JMS\Serializer\Annotation as JMS;


/**
 * Request definition of CreateRequest
 *
 * @package Realshadow\RequestDeserializer\Testing\Fixtures\Schema
 */
class CreateRequest implements RequestInterface
{

    /**
     * @var string $name
     *
     * @JMS\Since("1.x")
     * @JMS\Type("string")
     * @JMS\SerializedName("name")
     */
    private $name;

    /**
     * @var float $mass
     *
     * @JMS\Since("1.x")
     * @JMS\Type("double")
     * @JMS\SerializedName("mass")
     */
    private $mass;

    /**
     * @var bool $habitable
     *
     * @JMS\Since("1.x")
     * @JMS\Type("boolean")
     * @JMS\SerializedName("habitable")
     */
    private $habitable;

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
        return base_path('tests/Fixtures/schema/convert/schema.json');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getMass()
    {
        return $this->mass;
    }

    /**
     * @return null|bool
     */
    public function getHabitable()
    {
        return $this->habitable;
    }

}
