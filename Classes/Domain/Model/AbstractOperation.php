<?php

namespace SourceBroker\Restify\Domain\Model;

/**
 * Class AbstractOperation
 */
abstract class AbstractOperation
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var string
     */
    protected $path = '/';

    /**
     * AbstractOperation constructor.
     *
     * @param string $key
     * @param array $params
     */
    public function __construct(string $key, array $params)
    {
        $this->key = $key;
        $this->method = $params['method'] ?? $this->method;
        $this->path = $params['path'] ?? $this->path;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

}
