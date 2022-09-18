<?php

namespace App\Controller;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CacheController extends AbstractController
{
    #[Route('cache', name: 'app_cache')]
    public function index(TagAwareCacheInterface $cache): Response
    {

        return $this->render('cache/index.html.twig', [
            'controller_name' => 'CacheController',
        ]);
    }
}
