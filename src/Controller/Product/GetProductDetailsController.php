<?php

namespace App\Controller\Product;

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
            return new JsonResponse($pool->getItem($item)->get(), Response::HTTP_OK, ["cache-control" => "cached item"], true);
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
        $productsDetailsItem->getItem($item);
        $productsDetailsItem->set($jsonProduct);
        $productsDetailsItem->tag("products");
        $productsDetailsItem->expiresAfter(60);
        $pool->save($productsDetailsItem);
        sleep(3); // TO TEST CACHE WAY
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
