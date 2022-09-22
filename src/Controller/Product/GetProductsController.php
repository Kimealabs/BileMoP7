<?php

namespace App\Controller\Product;

use App\Entity\Product;
use App\Repository\ProductRepository;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security;
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


/**
 * @OA\Get(
 *      description="List all products with paginate",
 *      summary="SHOW ALL PRODUCTS"
 * )
 * @OA\Response(
 *      response=200,
 *      description="Use this method to gel all products.",
 *      @OA\JsonContent(
 *          @OA\Property(property="meta", type="string",
 *              example={"total_products":2451, "Max_page_size":5}
 *          ),
 *          @OA\Property(property="links", type="string",
 *              example={
 *                  {"href":"/api/products?page=3&page_size=5", "rel":"self", "method":"GET"},
 *                  {"href":"/api/products?page=1&page_size=5", "rel":"first_page", "method":"GET"},
 *                  {"href":"/api/products?page=5&page_size=5", "rel":"last_page", "method":"GET"},
 *                  {"href":"/api/products?page=4&page_size=5", "rel":"next_page", "method":"GET"},
 *                  {"href":"/api/products?page=2&page_size=5", "rel":"previous_page", "method":"GET"}
 *              }
 *          ),
 *          @OA\Property(property="products", type="array",
 *              @OA\Items(ref=@Model(type=Product::class, groups={"getProducts"}))
 *          )
 *      )
 * )
 * @OA\Response(
 *      response=401,
 *      description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token",
 *      @OA\JsonContent(
 *        @OA\Property(property="code", type="string", example="code: 401"),
 *        @OA\Property(property="message", type="string", example="JWT Token not found | Expired JWT Token | Invalid JWT Token")
 *      )
 * )
 * @OA\Response(
 *      response=404,
 *      description="NOT FOUND - NO PRODUCT IN DATABASE",
 *      @OA\JsonContent(
 *        @OA\Property(property="code", type="string", example="404"),
 *        @OA\Property(property="message", type="string", example="No product in database")
 *      )
 * )
 * @OA\Parameter(
 *      name="page",
 *      in="query",
 *      description="Page number",
 *      @OA\Schema(type="int")
 * )
 * @OA\Parameter(
 *      name="page_size",
 *      in="query",
 *      description="Number of products per page",
 *      @OA\Schema(type="int")
 * )

 * @OA\Tag(name="Products")
 */

#[Route('/api/products', name: 'app_products_list', methods: ['GET'], stateless: true)]
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
                    "total_products" => $totalProducts,
                    "Max_page_size" => 5
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
