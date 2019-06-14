<?php
declare(strict_types=1);

namespace SourceBroker\Restify\Transformer;

/**
 * Interface TransformerInterface
 *
 * @package SourceBroker\Restify\Transformer
 */
interface TransformerInterface
{
    /**
     * @param mixed $property
     *
     * @return mixed
     */
    public function serialize($property);
}
