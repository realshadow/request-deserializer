<?php

namespace Realshadow\RequestDeserializer\Contracts;

/**
 * Contract describing functionality all incoming request entities should impelement
 *
 * @package Realshadow\RequestDeserializer\Contracts
 * @author Lukáš Homza <lukashomz@gmail.com>
 */
interface RequestInterface
{

    /**
     * Checks if this request should be validated via JSON schema
     *
     * @return bool
     */
    public static function shouldValidate();

    /**
     * This method should return request data version used during deserialization
     *
     * @return string
     */
    public static function getVersionConstraint();

    /**
     * This method should return path to entity schema
     *
     * @return string
     */
    public function getSchema();

}
