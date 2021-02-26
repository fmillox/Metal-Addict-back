<?php

namespace App\Controller\Api;

use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Repository\ReviewRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

class ReviewController extends AbstractController
{
    /**
     * @Route("/api/review", name="api_review", methods={"GET"})
     */
    public function list(Request $request, EventRepository $eventRepository, UserRepository $userRepository, ReviewRepository $reviewRepository, SerializerInterface $serializer): Response
    {
        $limit = trim($request->query->get('limit', ''));
        $setlistId = trim($request->query->get('setlistId', ''));
        $userId = trim($request->query->get('user', ''));
        $order = strtoupper(trim($request->query->get('order', '')));

        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'ASC';
        }

        if (strlen($limit) > 0) {
            if (!is_numeric($limit)) {
                return $this->json(['error' => 'limit not a number'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $reviews = $reviewRepository->findBy([], ['createdAt' => $order], $limit);
        } 
        
        elseif (strlen($setlistId) > 0) {
            $event = $eventRepository->findBy(['setlistId' => $setlistId]);
            if (!$event) {
                return $this->json(['error' => 'event not found'], Response::HTTP_NOT_FOUND);
            }
            $reviews = $reviewRepository->findBy(['event' => $event], ['createdAt' => $order]);
        } 
        
        elseif (strlen($userId) > 0) {
            if (!is_numeric($userId)) {
                return $this->json(['error' => 'user not a number'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $user = $userRepository->find($userId);
            if (!$user) {
                return $this->json(['error' => 'user not found'], Response::HTTP_NOT_FOUND);
            }
            $reviews = $reviewRepository->findBy(['user' => $user], ['createdAt' => $order]);
        } 
        
        else {
            return $this->json(['error' => 'parameters expected not found'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $json = $serializer->serialize($reviews, 'json', ['groups' => 'list_reviews']);

        return new Response($json, Response::HTTP_OK, ['content-type' => 'application/json']);
    }
}
