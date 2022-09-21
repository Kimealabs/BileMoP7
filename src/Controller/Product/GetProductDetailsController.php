<?php

namespace App\Controller\Product;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/api/products/{id}', name: 'app_products_details', methods: ['GET'], requirements: ['id' => '\d+'], stateless: true)]
#[OA\Get(
    path: '/api/products/{id}',
    summary: "SHOW PRODUCT DETAILS",
    description: "PRODUCT DETAILS BY id",
    responses: [
        new OA\Response(response: 200, description: 'OK - id Products details'),
        new OA\Response(response: 404, description: 'NOT FOUND - This Product do not exist'),
        new OA\Response(response: 401, description: 'UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token')
    ]
)]
#[OA\Parameter(
    name: 'id',
    in: 'path',
    required: true,
    description: 'The idendifiant of product',
    schema: new OA\Schema(type: 'integer')
)]
#[OA\Tag(name: 'Products')]
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
