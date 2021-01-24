<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Provider\ApiResourcePath;

use Generator;

interface ApiResourcePathProvider
{
    /**
     * @return Generator<string>
     */
    public function getAll(): Generator;
}
