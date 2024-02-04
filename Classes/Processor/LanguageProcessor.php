<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Processor;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LanguageProcessor implements ProcessorInterface
{
    public function process(Request $request, ResponseInterface &$response): void
    {
        if (!$GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface) {
            throw new RuntimeException(
                sprintf('`TYPO3_REQUEST` is not an instance of `%s`', ServerRequestInterface::class),
                1580483236906
            );
        }

        $languageHeader = $GLOBALS['TYPO3_REQUEST']->getHeader($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['languageHeader']);
        $languageUid = (int)(!empty($languageHeader) ? array_shift($languageHeader) : 0);
        $language = $request->get('site') ? $request->get('site')->getLanguageById($languageUid) : null;
        if ($language) {
            GeneralUtility::makeInstance(Context::class)
                ->setAspect('language', LanguageAspectFactory::createFromSiteLanguage($language));
            $GLOBALS['TYPO3_REQUEST'] = $GLOBALS['TYPO3_REQUEST']->withAttribute('language', $language);
        }
    }
}
