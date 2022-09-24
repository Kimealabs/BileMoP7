<?php

namespace App\Services;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheTools extends AbstractController
{
    private $item;
    private $pool;

    public function __construct(TagAwareCacheInterface $tagAwareCacheInterface)
    {
        $this->pool = $tagAwareCacheInterface;
    }

    public function setItem(string $item): bool
    {
        $this->item = $item;
        if ($this->pool->hasItem($this->item)) {
            return true;
        }
        return false;
    }

    public function getItem()
    {
        return $this->pool->getItem($this->item)->get();
    }

    public function deleteTags($tags)
    {
        $this->pool->invalidateTags($tags);
    }

    public function saveItem(string $tag, string $jsonValue): void
    {
        $poolItem = $this->pool->getItem($this->item);
        $poolItem->set($jsonValue);
        $poolItem->tag($tag);
        $poolItem->expiresAfter(60);
        $this->pool->save($poolItem);
        sleep(3); // TEST CACHE WAY
    }
}
