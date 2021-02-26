<?php

namespace App\Controller\Api;

use App\Service\FanartApi;
use App\Service\SetlistApi;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventController extends AbstractController
{
    /**
     * @Route("/api/event/{setlistId<\w+>}", name="api_event", methods={"GET"})
     */
    public function find(string $setlistId, SetlistApi $setlistApi, FanartApi $fanartApi): Response
    {
        $setlist = $setlistApi->findSetlist($setlistId);
        if ($setlist['httpStatusCode'] === Response::HTTP_NOT_FOUND) {
            return $this->json(['error' => 'setlistId not found'], Response::HTTP_NOT_FOUND);
        }
        unset($setlist['httpStatusCode']);

        $musicbrainzId = $setlist['artist']['mbid'];
        $bandImages = $fanartApi->getImages($musicbrainzId);

        return $this->json([
            'setlist' => $setlist,
            'bandImages' => $bandImages
        ], Response::HTTP_OK);
    }
}
