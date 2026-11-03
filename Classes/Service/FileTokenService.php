<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use SourceBroker\T3api\Configuration\Configuration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Token required to create a file reference by `uidLocal`. It confirms that the client got the file from the API
 * (upload response or an operation offering files for selection). The token is stateless (HMAC):
 * `<expiration timestamp>.<signature>`.
 *
 * A token created for a logged in frontend user is valid for this user only. A token created for an anonymous user
 * (e.g. file attached to an application form) is valid for everybody who has it - it is returned only in the response
 * for this user.
 */
class FileTokenService implements SingletonInterface
{
    /**
     * @var array<int, true> UIDs of files granted in the current request
     */
    protected array $grantedFileUids = [];

    protected bool $tokensIssued = false;

    public function __construct(protected readonly ?Context $context = null) {}

    /**
     * Called at the beginning and at the end of an API request - grants and issued tokens belong to one request.
     */
    public function reset(): void
    {
        $this->grantedFileUids = [];
        $this->tokensIssued = false;
    }

    /**
     * Whether a token was created in the current request - such response is not cached, as tokens are created for
     * the current user and expire.
     */
    public function hasIssuedTokens(): bool
    {
        return $this->tokensIssued;
    }

    /**
     * Grants a token for the file - it is added to the file when the response of the current request is serialized.
     * Files uploaded by `FileUploadService` are granted automatically. Call it in custom code returning files which
     * can be referenced (e.g. a custom upload).
     */
    public function grant(int $fileUid): void
    {
        $this->grantedFileUids[$fileUid] = true;
    }

    public function hasGrantedFiles(): bool
    {
        return $this->grantedFileUids !== [];
    }

    public function isGranted(int $fileUid): bool
    {
        return isset($this->grantedFileUids[$fileUid]);
    }

    /**
     * Token added to the file in a response: for every file when the operation enabled tokens for all files
     * (`fileTokens` in its `normalizationContext`), otherwise only for a file granted in the current request.
     */
    public function getTokenForResponse(int $fileUid, bool $tokensForAllFiles): ?string
    {
        if ($fileUid <= 0 || (!$tokensForAllFiles && !$this->isGranted($fileUid))) {
            return null;
        }

        return $this->createToken($fileUid);
    }

    /**
     * Marks the current request as issuing tokens (see `hasIssuedTokens()`), also when called by custom code.
     */
    public function createToken(int $fileUid): string
    {
        $this->tokensIssued = true;
        $expires = $this->getTimestamp() + Configuration::getFileTokenLifetime();

        return $expires . '.' . $this->sign($fileUid, $this->getFrontendUserUid(), $expires);
    }

    public function isValidToken(string $token, int $fileUid): bool
    {
        [$expires, $signature] = array_pad(explode('.', $token, 2), 2, '');
        if (preg_match('/^\d+\z/', $expires) !== 1 || (int)$expires < $this->getTimestamp() || $signature === '') {
            return false;
        }

        foreach (array_unique([$this->getFrontendUserUid(), 0]) as $userUid) {
            if (hash_equals($this->sign($fileUid, $userUid, (int)$expires), $signature)) {
                return true;
            }
        }

        return false;
    }

    protected function sign(int $fileUid, int $userUid, int $expires): string
    {
        $encryptionKey = (string)($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] ?? '');
        if ($encryptionKey === '') {
            throw new \RuntimeException('TYPO3 encryption key is required to sign file tokens', 1793700001);
        }

        return hash_hmac('sha256', implode('|', ['t3api-file', $fileUid, $userUid, $expires]), $encryptionKey . self::class);
    }

    protected function getFrontendUserUid(): int
    {
        return (int)$this->getContext()->getPropertyFromAspect('frontend.user', 'id', 0);
    }

    protected function getTimestamp(): int
    {
        return (int)$this->getContext()->getPropertyFromAspect('date', 'timestamp', time());
    }

    protected function getContext(): Context
    {
        return $this->context ?? GeneralUtility::makeInstance(Context::class);
    }
}
