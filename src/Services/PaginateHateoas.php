<?php

namespace App\Services;

use App\Services\Hateoas;
use App\Services\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginateHateoas
{
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack->getCurrentRequest();
    }

    public function createPaginateLinks(Hateoas $hateoas, Paginator $paginator): array
    {
        //$route = $request->attributes->get('_route');
        $route = $this->requestStack->get('_route');
        $links = [
            $hateoas->createLink($route, 'GET', 'self', ["page" => $paginator->getCurrentPage(), "page_size" => $paginator->getPageSize()]),
            $hateoas->createLink($route, 'GET', 'first_page', ["page" => 1, "page_size" => $paginator->getPageSize()]),
            $hateoas->createLink($route, 'GET', 'last_page', ["page" => $paginator->getTotalPages(), "page_size" => $paginator->getPageSize()])
        ];
        if ($paginator->getNextPage() !== null) {
            $links[] = $hateoas->createLink($route, 'GET', 'next_page', ["page" => $paginator->getNextPage(), "page_size" => $paginator->getPageSize()]);
        }

        if ($paginator->getPreviousPage() !== null) {
            $links[] = $hateoas->createLink($route, 'GET', 'previous_page', ["page" => $paginator->getPreviousPage(), "page_size" => $paginator->getPageSize()]);
        }

        $response = [
            "meta" => [
                "total_items" => $paginator->getTotalItems(),
                "Max_page_size" => $paginator->getLimit()
            ],
            "links" => $links
        ];
        return $response;
    }
}
