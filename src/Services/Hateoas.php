<?php

namespace App\Services;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class Hateoas extends AbstractController
{

    public function createLink(string $path, string $method, string $rel, array $arguments): array
    {
        $response = [
            "href" => $this->generateUrl($path, $arguments, UrlGeneratorInterface::ABSOLUTE_URL),
            "rel" => $rel,
            "method" => $method
        ];
        return $response;
    }
}
