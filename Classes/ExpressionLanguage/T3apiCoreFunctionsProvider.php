<?php
declare(strict_types=1);

namespace SourceBroker\T3api\ExpressionLanguage;

use SourceBroker\T3api\Service\UrlService;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class T3apiCoreFunctionsProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[]
     */
    public function getFunctions(): array
    {
        return [
            $this->getForceAbsoluteUrlFunction(),
        ];
    }

    protected function getForceAbsoluteUrlFunction(): ExpressionFunction
    {
        return new ExpressionFunction(
            'force_absolute_url',
            static function () {
            },
            static function ($existingVariables, string $url, string $fallbackHost): string {
                return UrlService::forceAbsoluteUrl($url, $fallbackHost);
            }
        );
    }
}
