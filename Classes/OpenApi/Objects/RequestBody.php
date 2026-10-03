<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OpenApi\Objects;

use SourceBroker\T3api\OpenApi\Utilities\Arr;

/**
 * @property string|null $description
 * @property \SourceBroker\T3api\OpenApi\Objects\MediaType[]|null $content
 * @property bool|null $required
 */
class RequestBody extends BaseObject
{
    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\MediaType[]|null
     */
    protected $content;

    /**
     * @var bool|null
     */
    protected $required;

    /**
     * @param string|null $description
     * @return static
     */
    public function description(?string $description): self
    {
        $instance = clone $this;

        $instance->description = $description;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\MediaType ...$content
     * @return static
     */
    public function content(MediaType ...$content): self
    {
        $instance = clone $this;

        $instance->content = $content ?: null;

        return $instance;
    }

    /**
     * @param bool|null $required
     * @return static
     */
    public function required(?bool $required = true): self
    {
        $instance = clone $this;

        $instance->required = $required;

        return $instance;
    }

    /**
     * @return array
     */
    protected function generate(): array
    {
        $content = [];
        foreach ($this->content ?? [] as $contentItem) {
            $content[$contentItem->mediaType ?? ''] = $contentItem;
        }

        return Arr::filter([
            'description' => $this->description,
            'content' => $content ?: null,
            'required' => $this->required,
        ]);
    }
}
