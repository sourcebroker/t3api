<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Security;

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\ExpressionLanguage\Resolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AbstractAccessChecker
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function getExpressionLanguageResolver(): Resolver
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
}
