<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Domain\Model;

class OpenApiSettings extends AbstractOperationResourceSettings
{
    protected string $entityName = '';

    protected string $tagName = '';

    protected string $tagDescription = '';

    /**
     * @param OpenApiSettings|null $base
     * @return OpenApiSettings
     */
    public static function create(
        array $attributes = [],
        ?AbstractOperationResourceSettings $base = null,
        string $entityName = ''
    ): AbstractOperationResourceSettings {
        $settings = parent::create($attributes);
        $settings->tagName = $attributes['title'] ?? $settings->tagName;
        $settings->tagDescription = $attributes['description'] ?? $settings->tagDescription;

        return $settings;
    }

    public function getTagName(): string
    {
        if ($this->tagName !== '') {
            return $this->tagName;
        }
        return $this->entityName;
    }

    public function getTagDescription(): string
    {
        if ($this->tagDescription !== '') {
            return $this->tagDescription;
        }
        return sprintf('Operations about %s', $this->entityName);
    }
}
