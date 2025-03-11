<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Factory;

use SourceBroker\T3api\Configuration\Configuration;
use SourceBroker\T3api\Provider\ApiResourcePath\ApiResourcePathProvider;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class ApiResourcePathProviderCollectionFactory
{
    public function __construct(
        #[AutowireIterator('t3api.api_resource_path_provider')]
        private readonly iterable $apiResourcePathProviders,
    ) {}

    public function get(): \Generator
    {
        foreach ($this->apiResourcePathProviders as $apiResourcePathProvider) {
            if (!$apiResourcePathProvider instanceof ApiResourcePathProvider) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'API resource path provider `%s` has to be an instance of `%s`',
                        $apiResourcePathProvider::class,
                        ApiResourcePathProvider::class
                    ),
                    1609066405400
                );
            }

            yield $apiResourcePathProvider;
        }

        foreach (Configuration::getApiResourcePathProviders() as $apiResourcePathProvider) {
            yield $apiResourcePathProvider;
        }
    }
}
