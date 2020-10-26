<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Cors;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class Request
{
    protected $request;

    public function __construct(\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        if ($this->isPreflight() && $this->request->headers->has('Access-Control-Request-Method')) {
            return $this->request->headers->get('Access-Control-Request-Method');
        }
        return '';
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        if ($this->isPreflight() && $this->request->headers->has('Access-Control-Request-Headers')) {
            return GeneralUtility::trimExplode(',', $this->request->headers->get('Access-Control-Request-Headers'));
        }
        return [];
    }

    /**
     * @return bool
     */
    public function isPreflight(): bool
    {
        return $this->request->getMethod() === 'OPTIONS';
    }

    /**
     * @return bool
     */
    public function isCrossOrigin(): bool
    {
        return
            $this->request->headers->has('origin') &&
            $this->request->headers->get('origin') !== $this->getSchemeAndHttpHost();
    }

    /**
     * @return string
     */
    public function getSchemeAndHttpHost(): string
    {
        return $this->request->server->get('REQUEST_SCHEME') . '://' . $this->request->server->get('HTTP_HOST');
    }

    /**
     * @return string
     */
    public function getOriginUri(): string
    {
        $uri = '';
        if ($this->request->headers->has('origin')) {
            $data = parse_url($this->request->headers->get('origin'));
            $uri = $data['scheme'] . '://' . $data['host'] . (isset($data['port']) ? ':' . $data['port'] : '');
        }
        return $uri;
    }

    /**
     * @return bool
     */
    public function hasCredentials(): bool
    {
        return
            $this->request->headers->has('cookie')
            || $this->request->headers->has('authorization')
            || ($this->request->server->has('SSL_CLIENT_VERIFY') && $this->request->server->get('SSL_CLIENT_VERIFY') !== 'NONE' );
    }

}
