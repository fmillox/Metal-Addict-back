<?php

namespace App\Controller\Api;

use App\Entity\Picture;
use App\Service\FileUploader;
use App\Service\ValidatorError;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use App\Repository\ReviewRepository;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PictureController extends AbstractController
{
    /**
     * @Route("/api/picture", name="api_picture_list", methods={"GET"})
     */
    public function listBy(Request $request, ReviewRepository $reviewRepository, EventRepository $eventRepository, UserRepository $userRepository, PictureRepository $pictureRepository, SerializerInterface $serializer): Response
    {
        $reviewId = trim($request->query->get('review', ''));
        $setlistId = trim($request->query->get('setlistId', ''));
        $userId = trim($request->query->get('user', ''));
        $order = strtoupper(trim($request->query->get('order', '')));

        if (!in_array($order, ['ASC', 'DESC'])) {
            return $this->json(['error' => 'order not a valid value'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (strlen($reviewId) > 0) {
            if (!is_numeric($reviewId)) {
                return $this->json(['error' => 'review not a number'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $review = $reviewRepository->find($reviewId);
            if (!$review) {
                return $this->json(['error' => 'review not found'], Response::HTTP_NOT_FOUND);
            }
            $pictures = $pictureRepository->findBy(['event' => $review->getEvent()], ['createdAt' => $order]);
        } 
        
        elseif (strlen($setlistId) > 0) {
            $event = $eventRepository->findBy(['setlistId' => $setlistId]);
            if ($event) {
                $pictures = $pictureRepository->findBy(['event' => $event], ['createdAt' => $order]);
            } else {
                $pictures = [];
            }
        } 
        
        elseif (strlen($userId) > 0) {
            if (!is_numeric($userId)) {
                return $this->json(['error' => 'user not a number'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $user = $userRepository->find($userId);
            if (!$user) {
                return $this->json(['error' => 'user not found'], Response::HTTP_NOT_FOUND);
            }
            $pictures = $pictureRepository->findBy(['user' => $user], ['createdAt' => $order]);
        } 
        
        else {
            return $this->json(['error' => 'parameters expected not found'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $json = $serializer->serialize($pictures, 'json', ['groups' => 'picture']);

        return new Response($json, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    /**
     * @Route("/api/picture/{setlistId<\w+>}", name="api_picture_add", methods={"POST"})
     */
    public function add(string $setlistId, EventRepository $eventRepository, Request $request, ValidatorError $validatorError, FileUploader $fileUploader, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $user = $this->getUser();

        $event = $eventRepository->findOneBy(['setlistId' => $setlistId]);
        if (!$event) {
            return $this->json(['error' => 'event not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$event->getUsers()->contains($user)) {
            return $this->json(['error' => 'event not associated to the user'], Response::HTTP_NOT_ACCEPTABLE);
        }

        $form = $this->createFormBuilder(null, ['csrf_protection' => false])
            ->add('image', FileType::class, [
                'constraints' => [
                    new NotBlank(),
                    new File([
                        'maxSize' => '5M',
                        'maxSizeMessage' => 'The file is too large. Allowed maximum size is 5M.',
                        'mimeTypes' => [
                            'image/png', 
                            'image/jpeg', 
                            'image/gif'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (png, jpeg or gif).'
                    ]),
                ],
            ])
            ->getForm();
        $form->submit(['image' => $request->files->get('image')]);

        if (!$form->isValid()) {
            return $this->json(['error' => $validatorError->make($form->getErrors(true))], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $picture = new Picture();
        $picture->setPath($fileUploader->upload($form->get('image')->getData(), 'pictures'));
        $picture->setEvent($event);
        $picture->setUser($user);
        $entityManager->persist($picture);
        $entityManager->flush();

        $json = $serializer->serialize($picture, 'json', ['groups' => 'picture']);

        return new Response($json, Response::HTTP_CREATED, ['content-type' => 'application/json']);
    }
}
