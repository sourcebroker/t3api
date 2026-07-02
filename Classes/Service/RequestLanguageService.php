<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RequestLanguageService
{
    public function withLanguageFromHeader(ServerRequestInterface $request): ServerRequestInterface
    {
        $languageHeader = $request->getHeader($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['languageHeader']);
        if ($languageHeader === []) {
            return $request;
        }

        $site = $request->getAttribute('site');
        $language = $request->getAttribute('language');
        if (!$site instanceof Site || !$language instanceof SiteLanguage) {
            return $request;
        }

        $languageUid = (int)array_shift($languageHeader);
        if ($language->getLanguageId() === $languageUid) {
            return $request;
        }

        $targetLanguage = $site->getLanguageById($languageUid);
        GeneralUtility::makeInstance(Context::class)->setAspect(
            'language',
            LanguageAspectFactory::createFromSiteLanguage($targetLanguage)
        );

        return $request
            ->withAttribute('t3apiHeaderLanguageRequest', true)
            ->withAttribute('language', $targetLanguage);
    }
}
