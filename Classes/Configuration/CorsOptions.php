<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Configuration;

class CorsOptions
{
    /**
     * @var bool
     */
    public $allowCredentials = false;

    /**
     * @var string[]
     */
    public $allowOrigin = [];

    /**
     * @var string[]
     */
    public $allowHeaders = [];

    /**
     * @var string[]
     */
    public $allowMethods = [];

    /**
     * @var string[]
     */
    public $exposeHeaders = [];

    /**
     * @var int
     */
    public $maxAge;

    /**
     * @var bool
     */
    public $originRegex = false;

    public function __construct(array $options)
    {
        $this->allowCredentials = isset($options['allowCredentials']) ? (bool)$options['allowCredentials'] : $this->allowCredentials;
        $this->allowOrigin = isset($options['allowOrigin']) ? (array)$options['allowOrigin'] : $this->allowOrigin;
        $this->allowHeaders = isset($options['allowHeaders']) ? (array)$options['allowHeaders'] : $this->allowHeaders;
        $this->allowHeaders = array_merge($this->allowHeaders, (isset($options['simpleHeaders']) ? (array)$options['simpleHeaders'] : []));
        $this->allowHeaders = array_map('strtolower', $this->allowHeaders);
        $this->allowMethods = isset($options['allowMethods']) ? array_map('strtoupper', (array)$options['allowMethods']) : $this->allowMethods;
        $this->exposeHeaders = isset($options['exposeHeaders']) ? (array)$options['exposeHeaders'] : $this->exposeHeaders;
        $this->maxAge = isset($options['maxAge']) ? (int)$options['maxAge'] : $this->maxAge;
        $this->originRegex = isset($options['originRegex']) ? (bool)$options['originRegex'] : $this->originRegex;
    }
}
