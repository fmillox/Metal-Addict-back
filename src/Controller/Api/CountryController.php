<?php

namespace App\Controller\Api;

use App\Repository\CountryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CountryController extends AbstractController
{
    /**
     * @Route("/api/country", name="api_country", methods={"GET"})
     */
    public function list(CountryRepository $countryRepository): Response
    {
        $countries = $countryRepository->findAllOrderByName();

        return $this->json($countries, Response::HTTP_OK);
    }
}
