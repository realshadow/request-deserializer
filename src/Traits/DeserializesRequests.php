<?php

namespace Realshadow\RequestDeserializer\Traits;

use Realshadow\RequestDeserializer\Http\Request\RequestHandler;


/**
 * Trait responsible for action override in controllers
 *
 * @package Realshadow\RequestDeserializer\Traits
 * @author Lukáš Homza <lukashomz@gmail.com>
 */
trait DeserializesRequests
{

    /**
     * Override of the method so we can inject our request object if it was bound
     *
     * @param string $method
     * @param array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {
        $requestHandler = app(RequestHandler::class);

        if ($requestHandler->isBound()) {
            $parameters = $requestHandler->bindRequest($parameters);
        }

        return parent::callAction($method, $parameters);
    }

}
