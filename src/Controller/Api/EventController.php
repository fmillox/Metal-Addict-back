<?php

namespace App\Controller\Api;

use App\Service\FanartApi;
use App\Service\SetlistApi;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventController extends AbstractController
{
    /**
     * @Route("/api/event", name="api_event_list", methods={"GET"})
     */
    public function listByUser(Request $request, UserRepository $userRepository, SerializerInterface $serializer): Response
    {
        $userId = trim($request->query->get('user', ''));
        $order = strtoupper(trim($request->query->get('order', '')));

        if (!in_array($order, ['ASC', 'DESC'])) {
            return $this->json(['error' => 'order not a valid value'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (strlen($userId) > 0) {
            if (!is_numeric($userId)) {
                return $this->json(['error' => 'user not a number'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $user = $userRepository->find($userId);
            if (!$user) {
                return $this->json(['error' => 'user not found'], Response::HTTP_NOT_FOUND);
            }
            $events = $user->getEvents();
        } 
        
        else {
            return $this->json(['error' => 'parameters expected not found'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $json = $serializer->serialize($events, 'json', ['groups' => 'list_events']);

        return new Response($json, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    /**
     * @Route("/api/event/{setlistId<\w+>}", name="api_event", methods={"GET"})
     */
    public function findBySetlist(string $setlistId, SetlistApi $setlistApi, FanartApi $fanartApi): Response
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
