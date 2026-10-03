<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OpenApi\Objects;

use SourceBroker\T3api\OpenApi\Contracts\SchemaContract;
use SourceBroker\T3api\OpenApi\Utilities\Arr;

/**
 * @property string|null $name
 * @property string|null $in
 * @property string|null $description
 * @property bool|null $required
 * @property bool|null $deprecated
 * @property bool|null $allowEmptyValue
 * @property string|null $style
 * @property bool|null $explode
 * @property bool|null $allowReserved
 * @property \SourceBroker\T3api\OpenApi\Objects\Schema|null $schema
 * @property mixed|null $example
 * @property \SourceBroker\T3api\OpenApi\Objects\Example[]|null $examples
 * @property \SourceBroker\T3api\OpenApi\Objects\MediaType[]|null $content
 */
class Parameter extends BaseObject
{
    public const IN_QUERY = 'query';
    public const IN_HEADER = 'header';
    public const IN_PATH = 'path';
    public const IN_COOKIE = 'cookie';

    public const STYLE_MATRIX = 'matrix';
    public const STYLE_LABEL = 'label';
    public const STYLE_FORM = 'form';
    public const STYLE_SIMPLE = 'simple';
    public const STYLE_SPACE_DELIMITED = 'spaceDelimited';
    public const STYLE_PIPE_DELIMITED = 'pipeDelimited';
    public const STYLE_DEEP_OBJECT = 'deepObject';

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $in;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var bool|null
     */
    protected $required;

    /**
     * @var bool|null
     */
    protected $deprecated;

    /**
     * @var bool|null
     */
    protected $allowEmptyValue;

    /**
     * @var string|null
     */
    protected $style;

    /**
     * @var bool|null
     */
    protected $explode;

    /**
     * @var string|null
     */
    protected $allowReserved;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Schema|null
     */
    protected $schema;

    /**
     * @var mixed|null
     */
    protected $example;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Example[]|null
     */
    protected $examples;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\MediaType[]|null
     */
    protected $content;

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function query(?string $objectId = null): self
    {
        return static::create($objectId)->in(static::IN_QUERY);
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function header(?string $objectId = null): self
    {
        return static::create($objectId)->in(static::IN_HEADER);
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function path(?string $objectId = null): self
    {
        return static::create($objectId)->in(static::IN_PATH);
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function cookie(?string $objectId = null): self
    {
        return static::create($objectId)->in(static::IN_COOKIE);
    }

    /**
     * @param string|null $name
     * @return static
     */
    public function name(?string $name): self
    {
        $instance = clone $this;

        $instance->name = $name;

        return $instance;
    }

    /**
     * @param string|null $in
     * @return static
     */
    public function in(?string $in): self
    {
        $instance = clone $this;

        $instance->in = $in;

        return $instance;
    }

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
     * @param bool|null $deprecated
     * @return static
     */
    public function deprecated(?bool $deprecated = true): self
    {
        $instance = clone $this;

        $instance->deprecated = $deprecated;

        return $instance;
    }

    /**
     * @param bool|null $allowEmptyValue
     * @return static
     */
    public function allowEmptyValue(?bool $allowEmptyValue = true): self
    {
        $instance = clone $this;

        $instance->allowEmptyValue = $allowEmptyValue;

        return $instance;
    }

    /**
     * @param string|null $style
     * @return static
     */
    public function style(?string $style): self
    {
        $instance = clone $this;

        $instance->style = $style;

        return $instance;
    }

    /**
     * @param bool|null $explode
     * @return static
     */
    public function explode(?bool $explode = true): self
    {
        $instance = clone $this;

        $instance->explode = $explode;

        return $instance;
    }

    /**
     * @param bool|null $allowReserved
     * @return static
     */
    public function allowReserved(?bool $allowReserved = true): self
    {
        $instance = clone $this;

        $instance->allowReserved = $allowReserved;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Contracts\SchemaContract|null $schema
     * @return static
     */
    public function schema(?SchemaContract $schema): self
    {
        $instance = clone $this;

        $instance->schema = $schema;

        return $instance;
    }

    /**
     * @param mixed|null $example
     * @return static
     */
    public function example($example): self
    {
        $instance = clone $this;

        $instance->example = $example;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Example ...$examples
     * @return static
     */
    public function examples(Example ...$examples): self
    {
        $instance = clone $this;

        $instance->examples = $examples ?: null;

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
     * @return array
     */
    protected function generate(): array
    {
        $examples = [];
        foreach ($this->examples ?? [] as $example) {
            $examples[$example->objectId ?? ''] = $example->toArray();
        }

        $content = [];
        foreach ($this->content ?? [] as $contentItem) {
            $content[$contentItem->mediaType ?? ''] = $contentItem;
        }

        return Arr::filter([
            'name' => $this->name,
            'in' => $this->in,
            'description' => $this->description,
            'required' => $this->required,
            'deprecated' => $this->deprecated,
            'allowEmptyValue' => $this->allowEmptyValue,
            'style' => $this->style,
            'explode' => $this->explode,
            'allowReserved' => $this->allowReserved,
            'schema' => $this->schema,
            'example' => $this->example,
            'examples' => $examples ?: null,
            'content' => $content ?: null,
        ]);
    }
}
