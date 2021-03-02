<?php

namespace App\Controller\Api;

use App\Entity\Event;
use App\Service\FanartApi;
use App\Service\SetlistApi;
use App\Repository\BandRepository;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        $json = $serializer->serialize($events, 'json', ['groups' => 'event']);

        return new Response($json, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    /**
     * @Route("/api/event/{setlistId<\w+>}", name="api_event_get", methods={"GET"})
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

    /**
     * @Route("/api/event/{setlistId<\w+>}", name="api_event_add", methods={"POST"})
     */
    public function add(string $setlistId, EventRepository $eventRepository, CountryRepository $countryRepository, BandRepository $bandRepository, SetlistApi $setlistApi, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $event = $eventRepository->findOneBy(['setlistId' => $setlistId]);
        if ($event === null) {
            $setlist = $setlistApi->findSetlist($setlistId);
            if ($setlist['httpStatusCode'] === Response::HTTP_NOT_FOUND) {
                return $this->json(['error' => 'setlistId not found'], Response::HTTP_NOT_FOUND);
            }
            unset($setlist['httpStatusCode']);

            $country = $countryRepository->findOneBy(['countryCode' => $setlist['venue']['city']['country']['code']]);
            if (!$country) {
                return $this->json(['error' => 'country not found'], Response::HTTP_NOT_FOUND);
            }
            
            $band = $bandRepository->findOneBy(['musicbrainzId' => $setlist['artist']['mbid']]);
            if (!$band) {
                return $this->json(['error' => 'band not found'], Response::HTTP_NOT_FOUND);
            }

            $event = new Event();
            $event->setSetlistId($setlistId);
            $event->setVenue($setlist['venue']['name']);
            $event->setCity($setlist['venue']['city']['name']);
            $event->setDate(\DateTime::createFromFormat('d-m-Y', $setlist['eventDate']));
            $event->setCountry($country);
            $event->setBand($band);
            $entityManager->persist($event);

        } elseif ($event->getUsers()->contains($user)) {
            return $this->json('user already associated with event', Response::HTTP_FORBIDDEN);
        }
        $event->addUser($user);
        $entityManager->flush();
            
        return $this->json(['message' => 'OK'], Response::HTTP_CREATED);
    }
}
