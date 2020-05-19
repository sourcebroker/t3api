<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Security;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\ExpressionLanguage\Resolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Configuration\TypoScript\ConditionMatching\ConditionMatcher;

class AbstractAccessChecker
{
    protected static function getExpressionLanguageResolver(): Resolver
    {
        static $expressionLanguageResolver;

        if ($expressionLanguageResolver === null) {
            $context = GeneralUtility::makeInstance(Context::class);
            try {
                if ($context->hasAspect('backend.user')) {
                    /** @var UserAspect $backendUserAspect */
                    $backendUserAspect = $context->getAspect('backend.user');
                    $backend = new \stdClass();
                    $backend->user = new \stdClass();
                    $backend->user->isAdmin = $backendUserAspect->get('isAdmin');
                    $backend->user->isLoggedIn = $backendUserAspect->get('isLoggedIn');
                    $backend->user->userId = $backendUserAspect->get('id');
                    $backend->user->userGroupList = implode(',', $backendUserAspect->get('groupIds'));
                }

                if ($context->hasAspect('frontend.user')) {
                    /** @var UserAspect $frontendUserAspect */
                    $frontendUserAspect = $context->getAspect('frontend.user');
                    $frontend = new \stdClass();
                    $frontend->user = new \stdClass();
                    $frontend->user->isLoggedIn = $frontendUserAspect->get('isLoggedIn');
                    $frontend->user->userId = $frontendUserAspect->get('id');
                    $frontend->user->userGroupList = implode(',', $frontendUserAspect->get('groupIds'));
                }
            } catch (AspectNotFoundException $e) {
                // this catch exists only to avoid IDE complaints - such error can not be thrown since `getAspect`
                // usages are wrapped with `hasAspect` conditions
            } catch (AspectPropertyNotFoundException $e) {
            }

            $expressionLanguageResolver = GeneralUtility::makeInstance(
                Resolver::class,
                't3api',
                [
                    'backend' => $backend ?? null,
                    'frontend' => $frontend ?? null,
                ]
            );
        }

        return $expressionLanguageResolver;
    }

    /**
     * @todo Remove when support for version lower than 9.4 is dropped
     * @deprecated
     */
    protected static function shouldUseLegacyCheckMethod(): bool
    {
        return version_compare(TYPO3_branch, '9.4', '<');
    }

    /**
     * @todo Remove when support for version lower than 9.4 is dropped
     * @deprecated
     */
    protected static function evaluateLegacyExpressionLanguage(string $expression, array $additionalVariables = [])
    {
        return static::getLegacyExpressionLanguageEvaluator()
            ->evaluate($expression, array_merge(static::getLegacyExpressionLanguageDefaultVariables(), $additionalVariables));
    }

    /**
     * @todo Remove when support for version lower than 9.4 is dropped
     * @deprecated
     */
    protected static function getLegacyExpressionLanguageDefaultVariables(): array
    {
        /** @var FrontendBackendUserAuthentication|null $backendUserAuthentication */
        $backendUserAuthentication = $GLOBALS['BE_USER'];

        $backend = new \stdClass();
        $backend->user = new \stdClass();
        $backend->user->isAdmin = $backendUserAuthentication && $backendUserAuthentication->isAdmin();
        $backend->user->isLoggedIn = (bool)$backendUserAuthentication;
        $backend->user->userId = $backendUserAuthentication && $backendUserAuthentication->user
            ? $backendUserAuthentication->user['uid'] : null;
        $backend->user->userGroupList = $backendUserAuthentication && $backendUserAuthentication->user
            ? $backendUserAuthentication->user['usergroup'] : '';

        /** @var FrontendUserAuthentication|null $frontendUserAuthentication */
        $frontendUserAuthentication = $GLOBALS['TSFE']->fe_user;

        $frontend = new \stdClass();
        $frontend->user = new \stdClass();
        $frontend->user->isLoggedIn = (bool)$frontendUserAuthentication;
        $frontend->user->userId = $frontendUserAuthentication && $frontendUserAuthentication->user ? $frontendUserAuthentication->user['uid'] : null;;
        $frontend->user->userGroupList = $frontendUserAuthentication && $frontendUserAuthentication->user
            ? $frontendUserAuthentication->user['usergroup'] : '';

        return [
            'backend' => $backend,
            'frontend' => $frontend,
        ];
    }

    /**
     * @todo Remove when support for version lower than 9.4 is dropped
     * @deprecated
     */
    protected static function getLegacyExpressionLanguageEvaluator(): ExpressionLanguage
    {
        static $expressionLanguageEvaluator;

        if ($expressionLanguageEvaluator === null) {
            $expressionLanguageEvaluator = new ExpressionLanguage();
            $expressionLanguageEvaluator->addFunction(new ExpressionFunction(
                'like',
                static function () {
                },
                static function ($arguments, $haystack, $needle) {
                    $searchFunc = new \ReflectionMethod(ConditionMatcher::class, 'searchStringWildcard');
                    $searchFunc->setAccessible(true);

                    return $searchFunc->invoke(new ConditionMatcher(), $haystack, $needle);
                }
            ));
        }

        return $expressionLanguageEvaluator;
    }
}
