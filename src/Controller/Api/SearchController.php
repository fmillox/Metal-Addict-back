<?php

namespace App\Controller\Api;

use App\Entity\Band;
use App\Service\FanartApi;
use App\Service\SetlistApi;
use App\Repository\CountryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    /**
     * @Route("/api/search/{id<\d+>}", name="api_search", methods={"GET"})
     */
    public function search(Band $band = null, Request $request, CountryRepository $countryRepository, SetlistApi $setlistApi, FanartApi $fanartApi): Response
    {
        if (!$band) {
            return $this->json(['error' => 'band not found'], Response::HTTP_NOT_FOUND);
        }

        $query = [];

        $cityName = trim($request->query->get('cityName', ''));
        if (strlen($cityName) > 0) {
            $query['cityName'] = $cityName;
        }

        $venueName = trim($request->query->get('venueName', ''));
        if (strlen($venueName) > 0) {
            $query['venueName'] = $venueName;
        }

        $countryId = trim($request->query->get('countryId', ''));
        if (strlen($countryId) > 0) {
            if (!is_numeric($countryId)) {
                return $this->json(['error' => 'countryId not a number'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $country = $countryRepository->find($countryId);
            if (!$country) {
                return $this->json(['error' => 'country not found'], Response::HTTP_NOT_FOUND);
            }
            $query['countryCode'] = $country->getCountryCode();
        }

        $year = trim($request->query->get('year', ''));
        if (strlen($year) > 0) {
            if (!is_numeric($year)) {
                return $this->json(['error' => 'year not a number'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $query['year'] = $year;
        }

        $p = trim($request->query->get('p', ''));
        if (strlen($p) > 0) {
            if (!is_numeric($p)) {
                return $this->json(['error' => 'p not a number'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } else {
            $p = '1';
        }
        $query['p'] = $p;

        $setlists = $setlistApi->searchSetlists($band->getMusicbrainzId(), $query);
        $setlists['bandImages'] = $fanartApi->getImages($band->getMusicbrainzId());

        return $this->json($setlists, Response::HTTP_OK);
    }
}
