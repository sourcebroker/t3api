<?php
declare(strict_types=1);

namespace SourceBroker\T3api\Provider\ApiResourcePath;

interface ApiResourcePathProvider
{
    /**
     * Returns iterable collection of absolute paths (strings) to the files
     * with potential API resource classes
     * @return iterable<string>
     */
    public function getAll(): iterable;
}
