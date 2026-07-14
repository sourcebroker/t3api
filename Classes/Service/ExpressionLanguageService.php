<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use SourceBroker\T3api\ExpressionLanguage\Resolver;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExpressionLanguageService
{
    public static function getT3apiExpressionLanguage(): ExpressionLanguage
    {
        return GeneralUtility::makeInstance(Resolver::class, 't3api', [])->getExpressionLanguage();
    }

    /**
     * Builds the `backend`/`frontend` expression variables shared by the `security` conditions
     * (`AbstractAccessChecker`) and the response cache conditions (`ResponseCacheService`), from
     * the `backend.user`/`frontend.user` Context aspects.
     *
     * Should be kept as close as possible to \TYPO3\CMS\Core\TypoScript\IncludeTree\Visitor\IncludeTreeConditionMatcherVisitor::initializeExpressionMatcherWithVariables
     *
     * @return array{backend: ?\stdClass, frontend: ?\stdClass}
     */
    public static function getUserVariables(Context $context): array
    {
        $backend = null;
        $frontend = null;

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
                $backend->user->userGroupIds = $backendUserAspect->get('groupIds');
            }

            if ($context->hasAspect('frontend.user')) {
                /** @var UserAspect $frontendUserAspect */
                $frontendUserAspect = $context->getAspect('frontend.user');
                $frontend = new \stdClass();
                $frontend->user = new \stdClass();
                $frontend->user->isLoggedIn = $frontendUserAspect->get('isLoggedIn');
                $frontend->user->userId = $frontendUserAspect->get('id');
                $frontend->user->userGroupList = implode(',', $frontendUserAspect->get('groupIds'));
                $frontend->user->userGroupIds = $frontendUserAspect->get('groupIds');
            }
        } catch (AspectPropertyNotFoundException $e) {
        }

        return [
            'backend' => $backend,
            'frontend' => $frontend,
        ];
    }
}
