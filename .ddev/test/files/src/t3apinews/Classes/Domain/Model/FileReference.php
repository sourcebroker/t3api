<?php

namespace SourceBroker\T3apinews\Domain\Model;

/**
 * Class FileReference
 */
class FileReference extends \GeorgRinger\News\Domain\Model\FileReference
{
    public function getImageThumbnail(): int
    {
        return $this->uid;
    }

    public function getImageOriginal(): int
    {
        return $this->uid;
    }
}
