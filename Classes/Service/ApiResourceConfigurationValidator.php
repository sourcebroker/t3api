<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use SourceBroker\T3api\Domain\Model\ApiResource;
use SourceBroker\T3api\Domain\Model\OperationInterface;

/**
 * Validates a fully built {@see ApiResource} for configuration mistakes that are only detectable
 * once every operation exists with its cascaded settings resolved. Add further configuration
 * rules here as small private methods, each invoked from {@see self::validate()}.
 */
class ApiResourceConfigurationValidator
{
    public function validate(ApiResource $apiResource): void
    {
        foreach ($apiResource->getOperations() as $operation) {
            $this->assertResponseCacheNotExplicitlyConfiguredOnNonGetOperation($apiResource, $operation);
            $this->assertCacheInvalidationNotExplicitlyConfiguredOnGetOperation($apiResource, $operation);
        }
    }

    /**
     * `cache` configures response caching, which only ever applies to a GET operation - declaring
     * it explicitly on a non-GET operation is always a configuration mistake, since the declared
     * block would cascade but never actually be read at runtime. Only an EXPLICIT per-operation
     * block is rejected - a block inherited from the resource level is a legitimate cascade (e.g.
     * a resource-level `cache` block reaching a non-GET operation, which simply ignores it at
     * runtime) and must never throw.
     */
    private function assertResponseCacheNotExplicitlyConfiguredOnNonGetOperation(
        ApiResource $apiResource,
        OperationInterface $operation
    ): void {
        if ($operation->isMethodGet() || !$operation->getResponseCacheSettings()->wasExplicitlyConfigured()) {
            return;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Operation `%s` of entity `%s` is not a GET operation but declares an explicit ' .
                '`attributes.cache` block - `cache` configures response caching, which only ever applies ' .
                'to a GET operation. Use `cacheInvalidation` instead to flush tags after this operation ' .
                'writes successfully.',
                $operation->getKey(),
                $apiResource->getEntity()
            ),
            1783923801687
        );
    }

    /**
     * `cacheInvalidation` flushes tags after a non-GET operation writes successfully and never
     * applies to GET - declaring it explicitly on a GET operation is always a configuration
     * mistake. Only an EXPLICIT per-operation block is rejected - a block inherited from the
     * resource level (e.g. a resource-level `cacheInvalidation` block reaching a GET operation,
     * which simply ignores it at runtime) is a legitimate cascade and must never throw.
     */
    private function assertCacheInvalidationNotExplicitlyConfiguredOnGetOperation(
        ApiResource $apiResource,
        OperationInterface $operation
    ): void {
        if (!$operation->isMethodGet() || !$operation->getCacheInvalidationSettings()->wasExplicitlyConfigured()) {
            return;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Operation `%s` of entity `%s` is a GET operation but declares an explicit ' .
                '`attributes.cacheInvalidation` block - `cacheInvalidation` flushes tags after a non-GET ' .
                'operation writes successfully, and never applies to GET. Use `cache` instead to configure ' .
                'response caching for this operation.',
                $operation->getKey(),
                $apiResource->getEntity()
            ),
            1783923801688
        );
    }
}
