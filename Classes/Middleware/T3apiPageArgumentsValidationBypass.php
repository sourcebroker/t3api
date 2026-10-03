<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SourceBroker\T3api\Routing\Enhancer\ResourceEnhancer;
use TYPO3\CMS\Core\Routing\PageArguments;

/**
 * API requests carry query arguments (filters, pagination, ordering) which are not page arguments
 * and are never signed with a cHash. With `cacheHash.enforceValidation` enabled, TYPO3's
 * PageArgumentValidator would answer such requests with 404 "&cHash empty". Excluding the API
 * arguments globally via `cacheHash.excludedParameters` is not an option, as it would also affect
 * regular pages (e.g. a `page` argument of a plugin).
 *
 * This middleware runs right before PageArgumentValidator and, for API requests only, hands it
 * page arguments without query arguments and with the route arguments (`t3apiResource`) marked as
 * static, so there is nothing left to validate. T3apiPageArgumentsRestorer (right after the validator)
 * puts the original page arguments back, so all following middlewares see the unchanged request.
 */
class T3apiPageArgumentsValidationBypass implements MiddlewareInterface
{
    public const ORIGINAL_ROUTING_ATTRIBUTE = 't3api.originalRouting';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $pageArguments = $request->getAttribute('routing');

        if (
            $pageArguments instanceof PageArguments
            && array_key_exists(ResourceEnhancer::PARAMETER_NAME, $pageArguments->getRouteArguments())
        ) {
            $request = $request
                ->withAttribute(self::ORIGINAL_ROUTING_ATTRIBUTE, $pageArguments)
                ->withAttribute('routing', new PageArguments(
                    $pageArguments->getPageId(),
                    $pageArguments->getPageType(),
                    $pageArguments->getRouteArguments(),
                    array_replace($pageArguments->getStaticArguments(), $pageArguments->getRouteArguments())
                ));
        }

        return $handler->handle($request);
    }
}
