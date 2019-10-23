<?php

namespace SourceBroker\T3api\Swagger\Model;

class Info
    extends AbstractModel
{

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $version = '';

    /**
     * @var string|null
     */
    protected $termsOfService;

    /**
     * @var string[]|null
     */
    protected $contact;

    /**
     * @var string[]|null
     */
    protected $license;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): Info
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Info
    {
        $this->description = $description;
        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): Info
    {
        $this->version = $version;
        return $this;
    }

    public function getTermsOfService(): ?string
    {
        return $this->termsOfService;
    }

    public function setTermsOfService(?string $termsOfService): Info
    {
        $this->termsOfService = $termsOfService;
        return $this;
    }

    public function getContact(): ?array
    {
        return $this->contact;
    }

    public function setContact(?array $contact): Info
    {
        $this->contact = $contact;
        return $this;
    }

    public function getLicense(): ?array
    {
        return $this->license;
    }

    public function setLicense(?array $license): Info
    {
        $this->license = $license;
        return $this;
    }

}
