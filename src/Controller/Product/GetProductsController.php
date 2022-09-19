<?php

namespace App\Controller\Product;

use App\Entity\Product;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Security;
use App\Repository\ProductRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/products', name: 'app_products_list', methods: ['GET'], stateless: true)]
#[OA\Get(
    path: '/api/products',
    description: "PRODUCTS LIST",
    responses: [
        new OA\Response(response: 200, description: 'OK - List of all Products'),
        new OA\Response(response: 404, description: 'NOT FOUND - No product in database'),
        new OA\Response(response: 401, description: 'UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token')
    ]
)]

#[OA\Parameter(
    name: 'page',
    in: 'query',
    description: 'The field used to choose number page',
    schema: new OA\Schema(type: 'integer')
)]
#[OA\Parameter(
    name: 'page_size',
    in: 'query',
    description: 'The field used to choose number of items by page',
    schema: new OA\Schema(type: 'integer')
)]
#[OA\Tag(name: 'Products')]
class GetProductsController extends AbstractController
{
    public function __invoke(
        Request $request,
        ProductRepository $productRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $pool
    ): JsonResponse {

        $page = (int) $request->query->get('page', 1);
        $page_size = (int) $request->query->get('page_size', 5);
        if ($page == 0) $page = 1;
        if ($page_size == 0) $page_size = 1;
        if ($page_size > 5) $page_size = 5;

        $totalProducts = $productRepository->getTotalProducts();
        $totalPages = ceil($totalProducts / $page_size);
        if ($totalPages < $page) $page = $totalPages;
        $nextPage = ($page < $totalPages) ? $page + 1 : null;
        $previousPage = ($page > 1) ? $page - 1 : null;
        $currentPage = $page;

        // Cache item - return cache if item exist
        $item = 'products-list-' . $page . '-' . $page_size;
        if ($pool->hasItem($item)) {
            return new JsonResponse($pool->getItem($item)->get(), Response::HTTP_OK, ["cache-control" => "max-age=60"], true);
        }



        $products = $productRepository->findByPaginate($page_size, $page);
        foreach ($products as $product) {
            $product->setLinks([
                [
                    "href" => $this->generateUrl('app_products_details', ["id" => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    "rel" => "self",
                    "method" => "GET"
                ]
            ]);
        }

        if ($products) {
            $content = [
                "meta" => [
                    "total products" => $totalProducts,
                    "Max page_size" => 5
                ],
                "links" => [
                    [
                        "href" => $this->generateUrl('app_products_list', ["page" => $currentPage, "page_size" => $page_size], UrlGeneratorInterface::ABSOLUTE_URL),
                        "rel" => "self",
                        "method" => "GET"
                    ],
                    [
                        "href" => $this->generateUrl('app_products_list', ["page" => 1, "page_size" => $page_size], UrlGeneratorInterface::ABSOLUTE_URL),
                        "rel" => "first page",
                        "method" => "GET"
                    ],
                    [
                        "href" => $this->generateUrl('app_products_list', ["page" => $totalPages, "page_size" => $page_size], UrlGeneratorInterface::ABSOLUTE_URL),
                        "rel" => "last page",
                        "method" => "GET"
                    ],

                ]
            ];
            if ($nextPage !== null) {
                $content["links"][] =
                    [
                        "href" => $this->generateUrl('app_products_list', ["page" => $nextPage, "page_size" => $page_size], UrlGeneratorInterface::ABSOLUTE_URL),
                        "rel" => "next page",
                        "method" => "GET"
                    ];
            }
            if ($previousPage !== null) {
                $content["links"][] =
                    [
                        "href" => $this->generateUrl('app_products_list', ["page" => $previousPage, "page_size" => $page_size], UrlGeneratorInterface::ABSOLUTE_URL),
                        "rel" => "previous page",
                        "method" => "GET"
                    ];
            }
            $content["products"] = $products;
            $jsonProductList = $serializer->serialize($content, 'json', ['groups' => 'getProducts']);
            $productsListItem = $pool->getItem($item);
            $productsListItem->set($jsonProductList);
            $productsListItem->tag("products");
            $productsListItem->expiresAfter(60);
            $pool->save($productsListItem);
            sleep(3); // TEST CACHE WAY
            return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
        }
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "No product in database");
    }
}
