<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Slot;

use SourceBroker\T3api\Domain\Model\OperationInterface;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EnrichSerializationContext
{
    public function execute(OperationInterface $operation, Request $request, array $attributes): array
    {
        $attributes['TYPO3_HOST_ONLY'] = GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY');
        $attributes['TYPO3_PORT'] = GeneralUtility::getIndpEnv('TYPO3_PORT');
        $attributes['TYPO3_REQUEST_HOST'] = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');
        $attributes['TYPO3_REQUEST_URL'] = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
        $attributes['TYPO3_REQUEST_SCRIPT'] = GeneralUtility::getIndpEnv('TYPO3_REQUEST_SCRIPT');
        $attributes['TYPO3_REQUEST_DIR'] = GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR');
        $attributes['TYPO3_SITE_URL'] = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
        $attributes['TYPO3_SITE_PATH'] = GeneralUtility::getIndpEnv('TYPO3_SITE_PATH');
        $attributes['TYPO3_SITE_SCRIPT'] = GeneralUtility::getIndpEnv('TYPO3_SITE_SCRIPT');
        $attributes['TYPO3_DOCUMENT_ROOT'] = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT');
        $attributes['TYPO3_SSL'] = GeneralUtility::getIndpEnv('TYPO3_SSL');
        $attributes['TYPO3_PROXY'] = GeneralUtility::getIndpEnv('TYPO3_PROXY');

        return [
            'operation' => $operation,
            'request' => $request,
            'attributes' => $attributes,
        ];
    }
}
