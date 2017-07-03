<?php

namespace Realshadow\RequestDeserializer\Http\Request;

use JMS\Serializer\EventDispatcher\Events;
use Mews\Purifier\Purifier;
use Realshadow\RequestDeserializer\Contracts\RequestInterface;
use Dingo\Api\Exception\ValidationHttpException;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;


/**
 * Handles request deserialization
 *
 * @package Realshadow\RequestDeserializer\Http\Request
 * @author Lukáš Homza <lhomza@webland.company>
 */
class RequestDeserializationHandler implements EventSubscriberInterface
{

    const NO_VALIDATE = 'no_validate';

    /**
     * @var Validator $validator
     */
    private $validator;

    /**
     * @var Purifier $purifier
     */
    private $purifier;

    /**
     * Recursively converts object to array
     *
     * @param \stdClass $data
     *
     * @return array
     */
    private function pack(\stdClass $data)
    {
        return json_decode(json_encode($data), true);
    }

    /**
     * Recursively converts array to object
     *
     * @param array|\stdClass $data
     *
     * @return \stdClass
     */
    private function unpack($data)
    {
        $data = json_decode(json_encode($data));

        return is_array($data) ? (object) $data : $data;
    }

    /**
     * RequestDeserializationHandler constructor.
     *
     * @param Validator $validator
     * @param Purifier $purifier
     */
    public function __construct(Validator $validator, Purifier $purifier)
    {
        $this->validator = $validator;
        $this->purifier = $purifier;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ['event' => Events::PRE_DESERIALIZE, 'method' => 'sanitize'],
            ['event' => Events::PRE_DESERIALIZE, 'method' => 'validate'],
        ];
    }

    /**
     * @param PreDeserializeEvent $event
     *
     * @return PreDeserializeEvent
     */
    public function sanitize(PreDeserializeEvent $event)
    {
        $data = $this->purifier->clean($event->getData());

        if (is_array($data)) {
            array_walk_recursive($data, function (&$value) {
                $value = is_string($value) ? htmlspecialchars($value) : $value;
            });
        } else {
            $data = htmlspecialchars($data);
        }

        $event->setData($data);

        return $event;
    }

    /**
     * @param PreDeserializeEvent $event
     *
     * @return PreDeserializeEvent
     * @throws \ReflectionException
     *
     * @throws \JsonSchema\Exception\ExceptionInterface
     * @throws \Dingo\Api\Exception\ValidationHttpException
     */
    public function validate(PreDeserializeEvent $event)
    {
        $shouldSkipValidation = $event->getContext()
            ->attributes
            ->get(static::NO_VALIDATE)
            ->getOrElse(false);

        if ( ! $shouldSkipValidation) {
            $entityClass = $event->getType()['name'];

            /** @var RequestInterface $entity */

            $reflectionClass = new \ReflectionClass($entityClass);
            if ( ! $reflectionClass->implementsInterface(RequestInterface::class)) {
                return $event;
            }

            unset($reflectionClass);

            $entity = app($entityClass);

            $retriever = new UriRetriever();
            $schema = $retriever->retrieve('file://' . $entity->getSchema());

            $data = $event->getData();
            if ( ! $data) {
                $data = new \stdClass;
            }

            $data = $this->unpack($data);

            $this->validator->validate($data, $schema);

            if ( ! $this->validator->isValid()) {
                throw new ValidationHttpException($this->validator->getErrors());
            }

            $event->setData(
                $this->pack($data)
            );
        }

        return $event;
    }

}
