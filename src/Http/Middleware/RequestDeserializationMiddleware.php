<?php

namespace Realshadow\RequestDeserializer\Http\Middleware;

use Dingo\Api\Http\Request;
use Realshadow\RequestDeserializer\Contracts\RequestInterface;
use Realshadow\RequestDeserializer\Http\Request\RequestHandler;
use Closure;


/**
 * Class RequestDeserializationMiddleware
 *
 * @package Realshadow\RequestDeserializer\Http\Middleware
 * @author Lukáš Homza <lukashomz@gmail.com>
 */
class RequestDeserializationMiddleware
{

    /**
     * Request handler for deserialization of incoming requests
     *
     * @var RequestHandler $requestHandler
     */
    private $requestHandler;

    /**
     * Request entity class
     *
     * @var null|string $requestEntity
     */
    private $requestEntity;

    /**
     * Argument position in controllers arguments
     *
     * @var int $argumentPosition
     */
    private $argumentPosition = 0;

    /**
     * Determine matched controller and action from request
     *
     * @param Request|\Illuminate\Http\Request $request
     *
     * @return array
     */
    private function parseMatchedRoute(Request $request)
    {
        $action = $request->route()->getAction()['uses'];

        return explode('@', $action instanceof \Closure ? '@' : $action);
    }

    /**
     * @param Request|\Illuminate\Http\Request $request
     *
     * @return bool
     * @throws \ReflectionException
     */
    private function shouldValidate(Request $request)
    {
        $shouldValidate = false;

        list($controller, $action) = $this->parseMatchedRoute($request);

        if ($controller && $action) {
            $reflection = new \ReflectionMethod($controller, $action);
            foreach ($reflection->getParameters() AS $position => $param) {
                if ($param->getClass() === null) {
                    continue;
                }

                # -- requests have to implement RequestInterface
                if (in_array(RequestInterface::class, $param->getClass()->getInterfaceNames(), true)) {
                    if ($param->getClass()->isInterface()) {
                        $this->requestEntity = get_class(
                            app($param->getClass()->getName())
                        );
                    } else {
                        $this->requestEntity = $param->getClass()->getName();
                    }

                    $this->argumentPosition = $position;

                    $shouldValidate = true;

                    break;
                }
            }
        }

        return $shouldValidate;
    }

    /**
     * RequestDeserializationMiddleware constructor.
     *
     * @param RequestHandler $requestHandler
     */
    public function __construct(RequestHandler $requestHandler)
    {
        $this->requestHandler = $requestHandler;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     * @throws \ReflectionException
     *
     * @throws \LogicException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException
     */
    public function handle($request, Closure $next)
    {
        if ( ! $this->shouldValidate($request)) {
            return $next($request);
        }

        $this->requestHandler->setArgumentPosition($this->argumentPosition);

        if ($request->method() === Request::METHOD_GET) {
            $this->requestHandler->hydrate($this->requestEntity, $request->query->all());
        } else {
            $this->requestHandler->deserialize($this->requestEntity, $request->getContent());
        }

        return $next($request);
    }

}
