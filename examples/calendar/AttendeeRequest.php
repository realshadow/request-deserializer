<?php

namespace App\Requests;

use JMS\Serializer\Annotation as JMS;
use Realshadow\RequestDeserializer\Contracts\RequestInterface;


class AttendeeRequest implements RequestInterface
{

    /**
     * @var string $email
     *
     * @JMS\Since("1.x")
     * @JMS\Type("string")
     * @JMS\SerializedName("email")
     */
    private $email;

    /**
     * @var int $perPage
     *
     * @JMS\Since("1.x")
     * @JMS\Type("boolean")
     * @JMS\SerializedName("required")
     */
    private $required;

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
        return resource_path('schemas/attendee.json');
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return int
     */
    public function getRequired()
    {
        return $this->required;
    }

}
