<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Annotation\ORM;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Cascade
{
    /**
     * @var array
     */
    public $values = [];

    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->values = (array)$values['value'];
        }
    }
}
