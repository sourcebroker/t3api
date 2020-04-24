<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Security;

use SourceBroker\T3api\Domain\Model\AbstractOperation;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\ExpressionLanguage\Resolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class OperationAccessChecker extends AbstractAccessChecker
{
    /**
     * @param AbstractOperation $operation
     * @param array $expressionLanguageVariables
     *
     * @return bool
     */
    public static function isGranted(AbstractOperation $operation, array $expressionLanguageVariables = []): bool
    {
        if (!$operation->getSecurity()) {
            return true;
        }

        $resolver = self::getExpressionLanguageResolver();
        $resolver->expressionLanguageVariables['t3apiOperation'] = $operation;
        $resolver->expressionLanguageVariables = array_merge(
            $resolver->expressionLanguageVariables,
            $expressionLanguageVariables
        );

        return $resolver->evaluate($operation->getSecurity());
    }

    /**
     * @param AbstractOperation $operation
     * @param array $expressionLanguageVariables
     *
     * @return bool
     */
    public static function isGrantedPostDenormalize(AbstractOperation $operation, array $expressionLanguageVariables = []): bool
    {
        if (!$operation->getSecurityPostDenormalize()) {
            return true;
        }

        $resolver = self::getExpressionLanguageResolver();
        $resolver->expressionLanguageVariables['t3apiOperation'] = $operation;
        $resolver->expressionLanguageVariables = array_merge(
            $resolver->expressionLanguageVariables,
            $expressionLanguageVariables
        );

        return $resolver->evaluate($operation->getSecurityPostDenormalize());
    }

    /**
     * @return Resolver
     */
    protected static function getExpressionLanguageResolver(): Resolver
    {
        static $expressionLanguageResolver;

        if (is_null($expressionLanguageResolver)) {
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
