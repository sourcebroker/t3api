<?php

namespace SourceBroker\T3api\Swagger\Model;

class Path
    extends AbstractKeyModel
{

    /**
     * @var Operation|null
     */
    protected $post;

    /**
     * @var Operation|null
     */
    protected $get;

    /**
     * @var Operation|null
     */
    protected $put;

    /**
     * @var Operation|null
     */
    protected $patch;

    /**
     * @var Operation|null
     */
    protected $delete;


    public function getPost(): ?Operation
    {
        return $this->post;
    }

    public function setPost(?Operation $post): Path
    {
        $this->post = $post;
        return $this;
    }

    public function getGet(): ?Operation
    {
        return $this->get;
    }

    public function setGet(?Operation $get): Path
    {
        $this->get = $get;
        return $this;
    }

    public function getPut(): ?Operation
    {
        return $this->put;
    }

    public function setPut(?Operation $put): Path
    {
        $this->put = $put;
        return $this;
    }

    public function getPatch(): ?Operation
    {
        return $this->patch;
    }

    public function setPatch(?Operation $patch): Path
    {
        $this->patch = $patch;
        return $this;
    }

    public function getDelete(): ?Operation
    {
        return $this->delete;
    }

    public function setDelete(?Operation $delete): Path
    {
        $this->delete = $delete;
        return $this;
    }

}
