<?php

declare(strict_types=1);

namespace SourceBroker\T3api\Service;

use TYPO3\CMS\Core\SingletonInterface;

class CacheTagCollector implements SingletonInterface
{
    protected bool $collecting = false;

    /**
     * @var array<string, true>
     */
    protected array $tags = [];

    public function start(): void
    {
        $this->collecting = true;
        $this->tags = [];
    }

    public function isCollecting(): bool
    {
        return $this->collecting;
    }

    public function addTags(string ...$tags): void
    {
        if (!$this->collecting) {
            return;
        }

        foreach ($tags as $tag) {
            $this->tags[$tag] = true;
        }
    }

    /**
     * Ends collecting and returns the unique tags gathered since start().
     *
     * @return string[]
     */
    public function stop(): array
    {
        $this->collecting = false;
        $tags = array_keys($this->tags);
        $this->tags = [];

        return $tags;
    }
}
