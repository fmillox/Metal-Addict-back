<?php

namespace App\Controller\Api;

use App\Entity\Review;
use App\Service\MyValidator;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReviewController extends AbstractController
{
    /**
     * @Route("/api/review", name="api_review_list", methods={"GET"})
     */
    public function listBy(Request $request, EventRepository $eventRepository, UserRepository $userRepository, ReviewRepository $reviewRepository, SerializerInterface $serializer): Response
    {
        $limit = trim($request->query->get('limit', ''));
        $setlistId = trim($request->query->get('setlistId', ''));
        $userId = trim($request->query->get('user', ''));
        $order = strtoupper(trim($request->query->get('order', '')));

        if (!in_array($order, ['ASC', 'DESC'])) {
            return $this->json(['error' => 'order not a valid value'], Response::HTTP_UNPROCESSABLE_ENTITY);
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

        $json = $serializer->serialize($reviews, 'json', ['groups' => 'review']);

        return new Response($json, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    /**
     * @Route("/api/review/{id<\d+>}", name="api_review_show", methods={"GET"})
     */
    public function show(Review $review = null, SerializerInterface $serializer): Response
    {
        if (!$review) {
            return $this->json(['error' => 'review not found'], Response::HTTP_NOT_FOUND);
        }

        $json = $serializer->serialize($review, 'json', ['groups' => 'review']);

        return new Response($json, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    /**
     * @Route("/api/review/{setlistId<\w+>}", name="api_review_post", methods={"POST"})
     */
    public function post(string $setlistId, EventRepository $eventRepository, ReviewRepository $reviewRepository, Request $request, SerializerInterface $serializer, MyValidator $myValidator, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $event = $eventRepository->findOneBy(['setlistId' => $setlistId]);
        if (!$event) {
            return $this->json(['error' => 'event not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$event->getUsers()->contains($user)) {
            return $this->json(['error' => 'event not associated to the user'], Response::HTTP_NOT_ACCEPTABLE);
        }

        $review = $reviewRepository->findOneBy(['user' => $user, 'event' => $event]);
        if ($review) {
            return $this->json(['error' => 'review already created for the event by the user'], Response::HTTP_NOT_ACCEPTABLE);
        }

        $review = $serializer->deserialize($request->getContent(), Review::class, 'json');

        $errors = $myValidator->validate($review);
        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $review->setEvent($event);
        $review->setUser($user);
        $entityManager->persist($review);
        $entityManager->flush();

        return $this->json(
            $review,
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('api_review_show', ['id' => $review->getId()])
            ],
            ['groups' => 'review']
        );
    }

    /**
     * @Route("/api/review/{id<\d+>}", name="api_review_put_and_patch", methods={"PUT", "PATCH"})
     */
    public function putAndPatch(Review $review = null, Request $request, SerializerInterface $serializer, MyValidator $myValidator, EntityManagerInterface $entityManager): Response
    {
        if (!$review) {
            return $this->json(['error' => 'review not found'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('edit', $review->getUser());

        $serializer->deserialize($request->getContent(), Review::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $review]);

        $errors = $myValidator->validate($review);
        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();

        return $this->json($review, Response::HTTP_OK, [], ['groups' => 'review']);
    }

    /**
     * @Route("/api/review/{id<\d+>}", name="api_review_delete", methods={"DELETE"})
     */
    public function delete(Review $review = null, EntityManagerInterface $entityManager): Response
    {
        if (!$review) {
            return $this->json(['error' => 'review not found'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('edit', $review->getUser());

        $entityManager->remove($review);
        $entityManager->flush();

        return $this->json(['message' => 'review deleted'], Response::HTTP_OK);
    }
}
