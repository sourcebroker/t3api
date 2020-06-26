<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Cors;

use Psr\Http\Message\ResponseInterface;

class Response
{
    protected $response;
    protected $allowedOrigin = '';
    protected $allowCredentials = false;
    protected $exposedHeaders = [];
    protected $isPreflight = false;
    protected $allowedMethods = [];
    protected $allowedHeaders = [];
    protected $maximumAge;

    public function __construct(ResponseInterface &$response)
    {
        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getAllowedOrigin(): string
    {
        return $this->allowedOrigin;
    }

    /**
     * @param string $allowedOrigin
     * @return void
     */
    public function setAllowedOrigin($allowedOrigin): void
    {
        $this->allowedOrigin = $allowedOrigin;
    }

    /**
     * @return bool
     */
    public function getAllowCredentials(): bool
    {
        return $this->allowCredentials;
    }

    /**
     * @param bool $allowCredentials
     * @return void
     */
    public function setAllowCredentials($allowCredentials): void
    {
        $this->allowCredentials = $allowCredentials;
    }

    /**
     * @return array
     */
    public function getExposedHeaders(): array
    {
        return $this->exposedHeaders;
    }

    /**
     * @param array $exposedHeaders
     * @return void
     */
    public function setExposedHeaders(array $exposedHeaders): void
    {
        $this->exposedHeaders = $exposedHeaders;
    }

    /**
     * @return bool
     */
    public function isPreflight(): bool
    {
        return $this->isPreflight;
    }

    /**
     * @param bool $isPreflight
     * @return void
     */
    public function setPreflight($isPreflight): void
    {
        $this->isPreflight = $isPreflight;
    }

    /**
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }

    /**
     * @param array $allowedMethods
     * @return void
     */
    public function setAllowedMethods(array $allowedMethods): void
    {
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * @return array
     */
    public function getAllowedHeaders(): array
    {
        return $this->allowedHeaders;
    }

    /**
     * @param array $allowedHeaders
     * @return void
     */
    public function setAllowedHeaders(array $allowedHeaders): void
    {
        $this->allowedHeaders = $allowedHeaders;
    }

    /**
     * @return string
     */
    public function getMaximumAge(): ?string
    {
        return $this->maximumAge;
    }

    /**
     * @param string $maximumAge
     * @return void
     */
    public function setMaximumAge(?string $maximumAge): void
    {
        $this->maximumAge = $maximumAge;
    }

    /**
     * Sends all HTTP headers and the body as necessary
     *
     * @return $this
     */
    public function process(): self
    {
        if ($this->getAllowedOrigin()) {
            $this->sendHeader('Access-Control-Allow-Origin', $this->getAllowedOrigin());
        }

        if ($this->getAllowCredentials()) {
            $this->sendHeader('Access-Control-Allow-Credentials', 'true');
        }

        if (count($this->getExposedHeaders()) && !empty($this->getExposedHeaders()[0])) {
            $this->sendHeader('Access-Control-Expose-Headers', $this->getExposedHeaders());
        }
        return $this;
    }

    public function processPreflight(): self
    {
        if ($this->isPreflight()) {
            if (count($this->getAllowedMethods())) {
                $this->sendHeader('Access-Control-Allow-Methods', $this->getAllowedMethods());
            }

            if (count($this->getAllowedHeaders())) {
                $this->sendHeader('Access-Control-Allow-Headers', $this->getAllowedHeaders());
            }

            if ($this->getMaximumAge()) {
                $this->sendHeader('Access-Control-Max-Age', (string)$this->getMaximumAge());
            }

            $this->response = $this->response->withStatus(204);
        }
        return $this;
    }

    /**
     * Sends an HTTP response header
     *
     * @param string $name
     * @param string|string[] $value
     * @return void
     */
    protected function sendHeader($name, $value): void
    {
        $this->response = $this->response->withAddedHeader($name, $value);
    }
}
