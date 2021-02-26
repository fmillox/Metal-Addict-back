<?php

namespace App\Controller\Api;

use App\Repository\BandRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BandController extends AbstractController
{
    /**
     * @Route("/api/band", name="api_band", methods={"GET"})
     */
    public function list(BandRepository $bandRepository): Response
    {
        $bands = $bandRepository->findAllOrderByName();

        return $this->json($bands, Response::HTTP_OK);
    }
}
