<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use Psr\Http\Message\ServerRequestInterface;
use SourceBroker\T3api\Exception\BadRequestException;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RequestLanguageService
{
    /**
     * Request attribute with the exception for an invalid language header. It is thrown in
     * `Bootstrap::process()`, so the client gets a regular API error response.
     */
    public const ATTRIBUTE_LANGUAGE_HEADER_EXCEPTION = 't3apiLanguageHeaderException';

    public function withLanguageFromHeader(ServerRequestInterface $request): ServerRequestInterface
    {
        $headerName = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['languageHeader'];
        $languageHeader = $request->getHeader($headerName);
        if ($languageHeader === []) {
            return $request;
        }

        $site = $request->getAttribute('site');
        $language = $request->getAttribute('language');
        if (!$site instanceof Site || !$language instanceof SiteLanguage) {
            return $request;
        }

        $headerValue = trim((string)array_shift($languageHeader));
        if ($headerValue === '') {
            return $request;
        }

        $targetLanguage = preg_match('/^\d+$/', $headerValue) === 1 ? $this->getAvailableLanguage($site, (int)$headerValue) : null;
        if ($targetLanguage === null) {
            return $request->withAttribute(
                self::ATTRIBUTE_LANGUAGE_HEADER_EXCEPTION,
                new BadRequestException('language_header', [$headerName, $headerValue], 1793641754)
            );
        }

        if ($language->getLanguageId() === $targetLanguage->getLanguageId()) {
            return $request;
        }

        GeneralUtility::makeInstance(Context::class)->setAspect(
            'language',
            LanguageAspectFactory::createFromSiteLanguage($targetLanguage)
        );

        return $request
            ->withAttribute('t3apiHeaderLanguageRequest', true)
            ->withAttribute('language', $targetLanguage);
    }

    /**
     * Same rule as for the language in URL (see TYPO3 `SiteBaseRedirectResolver`): a disabled language
     * is available only for a backend user with access to it.
     */
    private function getAvailableLanguage(Site $site, int $languageId): ?SiteLanguage
    {
        $language = $site->getAllLanguages()[$languageId] ?? null;
        if ($language === null || $language->isEnabled()) {
            return $language;
        }

        $backendUser = $GLOBALS['BE_USER'] ?? null;

        return $backendUser instanceof BackendUserAuthentication && $backendUser->checkLanguageAccess($languageId)
            ? $language
            : null;
    }
}
