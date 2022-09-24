<?php

namespace App\Controller\Product;

use App\Entity\Product;
use App\Services\Hateoas;
use App\Services\Paginator;
use App\Services\CacheTools;
use OpenApi\Annotations as OA;
use App\Services\PaginateHateoas;
use App\Repository\ProductRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
 *      @OA\Schema(type="integer")
 * )
 * @OA\Parameter(
 *      name="page_size",
 *      in="query",
 *      description="Number of products per page",
 *      @OA\Schema(type="integer")
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
        Paginator $paginator,
        Hateoas $hateoas,
        PaginateHateoas $paginateHateoas,
        CacheTools $cacheTools
    ): JsonResponse {

        $totalProducts = $productRepository->getTotalProducts();

        $paginator->setParams($totalProducts, $request->query->get('page'), $request->query->get('page_size'));

        // Cache item - return cache if item exist
        $item = 'products-list-' . $paginator->getCurrentPage() . '-' . $paginator->getPageSize();
        //if ($cacheTools->findItem($item)) return new JsonResponse($cacheTools->findItem($item));
        if ($cacheTools->setItem($item)) {
            return new JsonResponse($cacheTools->getItem(), Response::HTTP_OK, ["cache-control" => "max-age=60"], true);
        }

        $products = $productRepository->findByPaginate($paginator->getPageSize(), $paginator->getCurrentPage());
        foreach ($products as $product) {
            $product->setLinks([
                $hateoas->createLink('app_products_list', 'GET', 'self', ["id" => $product->getId()])
            ]);
        }

        if ($products) {
            $content = $paginateHateoas->createPaginateLinks($hateoas, $paginator);
            $content["products"] = $products;

            $jsonProductList = $serializer->serialize($content, 'json', ['groups' => 'getProducts']);
            $cacheTools->saveItem('products', $jsonProductList);

            return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
        }
        throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "No product in database");
    }
}
