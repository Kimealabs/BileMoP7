<?php

namespace App\Services;

class Paginator
{
    const LIMITSIZE = 5;

    protected $totalItems = null;
    protected $pageSize = null;
    protected $nextPage = null;
    protected $previousPage = null;
    protected $currentPage = null;
    protected $totalPages = null;

    public function setParams(int $totalItems, $currentPage = null, $pageSize = null)
    {
        $this->totalItems = $totalItems;
        $this->currentPage = (int) ($currentPage == null || intval($currentPage) == 0) ? 1 : $currentPage;
        $this->pageSize = (int) ($pageSize == null || intval($pageSize) > self::LIMITSIZE || intval($pageSize == 0)) ? self::LIMITSIZE : $pageSize;
        $this->calculateParams();
    }

    private function calculateParams(): void
    {
        $this->totalPages = (int) ceil($this->totalItems / $this->pageSize);
        if ($this->totalPages < $this->currentPage) $this->currentPage = $this->totalPages;
        $this->nextPage = ($this->currentPage < $this->totalPages) ? $this->currentPage + 1 : null;
        $this->previousPage = ($this->currentPage > 1) ? $this->currentPage - 1 : null;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getNextPage(): ?int
    {
        return $this->nextPage;
    }

    public function getPreviousPage(): ?int
    {
        return $this->previousPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getLimit(): int
    {
        return self::LIMITSIZE;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }
}
