<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Configuration;

class CorsOptions
{
    public bool $allowCredentials = false;

    public array $allowOrigin = [];

    public array $allowHeaders = [];

    public array $allowMethods = [];

    public array $exposeHeaders = [];

    public ?int $maxAge = null;

    public bool $originRegex = false;

    public function __construct(array $options)
    {
        $this->allowCredentials = isset($options['allowCredentials']) ? (bool)$options['allowCredentials'] : $this->allowCredentials;
        $this->allowOrigin = isset($options['allowOrigin']) ? (array)$options['allowOrigin'] : $this->allowOrigin;
        $this->allowHeaders = isset($options['allowHeaders']) ? (array)$options['allowHeaders'] : $this->allowHeaders;
        $this->allowHeaders = array_merge(
            $this->allowHeaders,
            (isset($options['simpleHeaders']) ? (array)$options['simpleHeaders'] : [])
        );
        $this->allowHeaders = $this->normalizeList($this->allowHeaders, 'strtolower');
        $this->allowMethods = $this->normalizeList((array)($options['allowMethods'] ?? $this->allowMethods), 'strtoupper');
        $this->exposeHeaders = $this->normalizeList((array)($options['exposeHeaders'] ?? $this->exposeHeaders), 'strtolower');
        $this->maxAge = isset($options['maxAge']) ? (int)$options['maxAge'] : $this->maxAge;
        $this->originRegex = isset($options['originRegex']) ? (bool)$options['originRegex'] : $this->originRegex;
    }

    private function normalizeList(array $values, callable $normalizeCase): array
    {
        $values = array_map(static fn($value): string => $normalizeCase(trim((string)$value)), $values);

        return array_values(array_unique(array_filter($values, static fn(string $value): bool => $value !== '')));
    }
}
