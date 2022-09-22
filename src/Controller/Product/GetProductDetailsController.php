<?php

namespace App\Controller\Product;

use App\Entity\Product;
use App\Repository\ProductRepository;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
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
 *      description="PRODUCT DETAILS BY id",
 *      summary="SHOW PRODUCT DETAILS"
 * )
 * @OA\Response(
 *      response=200,
 *      description="Use this method to gel all products.",
 *      @OA\JsonContent(
 *          @OA\Property(property="links", type="string",
 *              example={
 *                  {"href":"/api/product/84615", "rel":"self", "method":"GET"},
 *              }
 *          ),
 *          @OA\Property(property="products", type="array",
 *              @OA\Items(ref=@Model(type=Product::class))
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
 *        @OA\Property(property="message", type="string", example="This Product do not exist")
 *      )
 * )
 * @OA\Parameter(
 *      name="id",
 *      in="path",
 *      description="The identifiant of product",
 *      @OA\Schema(type="integer")
 * )
 * @OA\Tag(name="Products")
 */

#[Route('/api/products/{id}', name: 'app_products_details', methods: ['GET'], requirements: ['id' => '\d+'], stateless: true)]
class GetProductDetailsController extends AbstractController
{
    public function __invoke(
        int $id,
        ProductRepository $productRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $pool
    ): JsonResponse {

        // Cache item - return cache if item exist
        $item = 'products-details-' . $id;
        if ($pool->hasItem($item)) {
            return new JsonResponse($pool->getItem($item)->get(), Response::HTTP_OK, ["cache-control" => "max-age=60"], true);
        }

        $product = $productRepository->find($id);

        if (!$product) {
            throw new HttpException(JsonResponse::HTTP_NOT_FOUND, "This Product don't exist");
        }

        $content = [
            "links" => [
                [
                    "href" => $this->generateUrl('app_products_details', ["id" => $id], UrlGeneratorInterface::ABSOLUTE_URL),
                    "rel" => "self",
                    "type" => "GET"
                ],
            ],
            "product" => $product
        ];

        // SET AND SAVE CACHE ITEM
        $jsonProduct = $serializer->serialize($content, 'json');
        $productsDetailsItem = $pool->getItem($item);
        $productsDetailsItem->set($jsonProduct);
        $productsDetailsItem->tag("products");
        $productsDetailsItem->expiresAfter(60);
        $pool->save($productsDetailsItem);
        sleep(3); // TO TEST CACHE WAY
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
