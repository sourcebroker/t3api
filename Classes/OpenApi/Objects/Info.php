<?php

declare(strict_types=1);

namespace SourceBroker\T3api\OpenApi\Objects;

use SourceBroker\T3api\OpenApi\Utilities\Arr;

/**
 * @property string|null $title
 * @property string|null $description
 * @property string|null $termsOfService
 * @property \SourceBroker\T3api\OpenApi\Objects\Contact|null $contact
 * @property \SourceBroker\T3api\OpenApi\Objects\License|null $license
 * @property string|null $version
 */
class Info extends BaseObject
{
    /**
     * @var string|null
     */
    protected $title;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string|null
     */
    protected $termsOfService;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\Contact|null
     */
    protected $contact;

    /**
     * @var \SourceBroker\T3api\OpenApi\Objects\License|null
     */
    protected $license;

    /**
     * @var string|null
     */
    protected $version;

    /**
     * @param string|null $title
     * @return static
     */
    public function title(?string $title): self
    {
        $instance = clone $this;

        $instance->title = $title;

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
     * @param string|null $termsOfService
     * @return static
     */
    public function termsOfService(?string $termsOfService): self
    {
        $instance = clone $this;

        $instance->termsOfService = $termsOfService;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\Contact|null $contact
     * @return static
     */
    public function contact(?Contact $contact): self
    {
        $instance = clone $this;

        $instance->contact = $contact;

        return $instance;
    }

    /**
     * @param \SourceBroker\T3api\OpenApi\Objects\License|null $license
     * @return static
     */
    public function license(?License $license): self
    {
        $instance = clone $this;

        $instance->license = $license;

        return $instance;
    }

    /**
     * @param string|null $version
     * @return static
     */
    public function version(?string $version): self
    {
        $instance = clone $this;

        $instance->version = $version;

        return $instance;
    }

    /**
     * @return array
     */
    protected function generate(): array
    {
        return Arr::filter([
            'title' => $this->title,
            'description' => $this->description,
            'termsOfService' => $this->termsOfService,
            'contact' => $this->contact,
            'license' => $this->license,
            'version' => $this->version,
        ]);
    }
}
