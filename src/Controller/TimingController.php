<?php

namespace App\Controller;

use App\Entity\LocalBusiness;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;

class TimingController extends AbstractController
{
    /**
     * @Route("/business/{id}/timing", name="business_fulfillment_timing", methods={"GET"})
     */
    public function fulfillmentTimingAction($id, Request $request,
        EntityManagerInterface $entityManager,
        CacheInterface $projectCache)
    {
        $data = [];


        return new JsonResponse($data);
    }
}
