<?php

namespace SourceBroker\T3api\Swagger\Model;

abstract class AbstractKeyModel
    extends AbstractModel
{

    /**
     * @var string
     */
    protected $_key;

    public function __construct(string $key)
    {
        $this->_key = $key;
    }

    public function _getKey(): string
    {
        return $this->_key;
    }

    public function _setKey(string $key): AbstractModel
    {
        $this->_key = $key;
        return $this;
    }

}
