<?php

namespace Realshadow\RequestDeserializer\Http\Request;

use Realshadow\RequestDeserializer\Contracts\RequestInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Serializer;


/**
 * Handles incomming requests
 *
 * @package Realshadow\RequestDeserializer\Http\Request
 * @author Lukáš Homza <lukashomz@gmail.com>
 */
class RequestHandler
{

    /**
     * @var Serializer $serializer
     */
    private $serializer;

    /**
     * @var RequestInterface $entity
     */
    private $entity;

    /**
     * @var int $argumentPosition
     */
    private $argumentPosition = 0;

    /**
     * @var bool $bound
     */
    private $bound = false;

    /**
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Method will deserialize request content and bind it to the provided entity
     *
     * @param string $entityClass
     * @param string $content
     * @param DeserializationContext $context
     */
    public function deserialize($entityClass, $content, DeserializationContext $context = null)
    {
        if ( ! $context) {
            $context = DeserializationContext::create()
                ->setVersion(call_user_func($entityClass . '::getVersionConstraint'));
        }

        if ( ! $content) {
            $content = '{}';
        }

        $this->entity = $this->serializer->deserialize($content, $entityClass, 'json', $context);
        $this->bound = true;
    }

    /**
     * Hydrating request objects from arrays
     *
     * @param string $entityClass
     * @param array $content
     */
    public function hydrate($entityClass, array $content)
    {
        $context = DeserializationContext::create()
            ->setSerializeNull(false)
            ->setVersion(call_user_func($entityClass . '::getVersionConstraint'))
            ->setAttribute(
                RequestDeserializationHandler::NO_VALIDATE,
                ! call_user_func($entityClass . '::shouldValidate')
            );


        $this->deserialize($entityClass, json_encode($content), $context);
    }

    /**
     * Position of our entity in method parameters
     *
     * @param int $position
     * @return $this
     */
    public function setArgumentPosition($position)
    {
        $this->argumentPosition = $position;

        return $this;
    }

    /**
     * Get the deserialized entity
     *
     * @return null|RequestInterface
     */
    public function getBoundRequest()
    {
        return $this->entity;
    }

    /**
     * Did we manage to find an entity for this request?
     *
     * @return bool
     */
    public function isBound()
    {
        return $this->bound;
    }

    /**
     * We inject our deserialized entity into method parameters
     *
     * @param $parameters
     * @return array
     */
    public function bindRequest(array $parameters)
    {
        # -- recalculate due to mixed keys
        $parameters = array_values($parameters);

        if ($parameters !== null && is_array($parameters)) {
            $parameters[$this->argumentPosition] = $this->entity;
        }

        return $parameters;
    }

}
