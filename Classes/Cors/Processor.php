<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Cors;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use Psr\Http\Message\ResponseInterface;

class Processor implements SingletonInterface
{
    /**
     * @var Options
     */
    protected $options;

    /**
     * @var Request;
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    public function __construct()
    {
        $this->options = $this->getObjectManager()->get(Options::class);
    }

    public function processPreflight(\Symfony\Component\HttpFoundation\Request $request, ResponseInterface &$response)
    {
        $this->setRequestAndResponse($request, $response);
        if ($this->checkOptionsAndCorsRequest() && $this->request->isPreflight()) {
            $this->response->setPreflight(true);
            if ($this->isMethodAllowed($this->request->getMethod())) {
                $this->response->setAllowedMethods([$this->request->getMethod()]);
            }

            foreach ($this->request->getHeaders() as $header) {
                if ($this->isHeaderAllowed($header)) {
                    $this->response->setAllowedHeaders($this->request->getHeaders());
                }
            }
            $this->response->setMaximumAge($this->options->getMaxAge());
            $response = $this->response->processPreflight()->getResponse();
        }
    }

    public function process(\Symfony\Component\HttpFoundation\Request $request, ResponseInterface &$response)
    {
        $this->setRequestAndResponse($request, $response);
        if ($this->checkOptionsAndCorsRequest()) {
            $originUri = $this->request->getOriginUri();
            if ($this->isOriginUriAllowed('*') && !$this->request->hasCredentials()) {
                $this->response->setAllowedOrigin('*');
            } elseif ($this->isOriginUriAllowed($originUri)) {
                $this->response->setAllowedOrigin($originUri);
            }

            if ($this->options->isAllowCredentials()) {
                $this->response->setAllowCredentials($this->options->isAllowCredentials());
            }
            $this->response->setExposedHeaders($this->options->getExposeHeaders());
            $response = $this->response->process()->getResponse();
        }
    }

    protected function checkOptionsAndCorsRequest()
    {
        return !empty($this->options->isEmpty()) && $this->request->isCrossOrigin();
    }

    protected function setRequestAndResponse(\Symfony\Component\HttpFoundation\Request $request, ResponseInterface &$response)
    {
        if (empty($this->request)) {
            $this->request = $this->getObjectManager()->get(Request::class, $request);
        }
        if (empty($this->response)) {
            $this->response = $this->getObjectManager()->get(Response::class, $response);
        }
    }

    protected function isMethodAllowed($method): bool
    {
        return in_array($method, $this->options->getSimpleMethods(), true) ||
            in_array($method, $this->options->getAllowMethods(), true);
    }

    protected function isHeaderAllowed($header): bool
    {
        $header = strtolower($header);

        return
            in_array($header, array_map('strtolower', $this->options->getSimpleHeaders()), true)
            || in_array($header, array_map('strtolower', $this->options->getAllowHeaders()), true);
    }

    protected function isOriginUriAllowed($originUri): bool
    {
        // Check for exact match
        if (in_array($originUri, $this->options->getAllowOrigin())) {
            return true;
        }
        // Check for pattern match
        if ($this->options->getAllowOriginPattern()) {
            if (preg_match('~^' . $this->options->getAllowOriginPattern() . '~i', $originUri) === 1) {
                return true;
            }
        }
        return false;
    }

    protected function getObjectManager(): ObjectManager
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }
}
