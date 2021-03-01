<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\EventRepository;
use App\Service\MyValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/api/user/{id<\d+>}", name="api_user_show", methods={"GET"})
     */
    public function show(User $user = null, SerializerInterface $serializer): Response
    {
        if (!$user) {
            return $this->json(['error' => 'user not found'], Response::HTTP_NOT_FOUND);
        }

        $json = $serializer->serialize($user, 'json', ['groups' => 'user']);

        return new Response($json, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    /**
     * @Route("/api/user/{setlistId<\w+>}", name="api_user_list", methods={"GET"})
     */
    public function listBySetlist(string $setlistId, EventRepository $eventRepository, SerializerInterface $serializer): Response
    {
        $event = $eventRepository->findOneBy(['setlistId' => $setlistId]);
        if ($event === null) {
            $users = [];
        } else {
            $users = $event->getUsers();
        }

        $json = $serializer->serialize($users, 'json', ['groups' => 'user']);

        return new Response($json, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    /**
     * @Route("/api/user", name="api_user_post", methods={"POST"})
     */
    public function post(Request $request, SerializerInterface $serializer, MyValidator $validator, UserPasswordEncoderInterface $userPasswordEncoder, EntityManagerInterface $entityManager): Response
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $errors = $validator->validate($user, null, ['register']);
        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($userPasswordEncoder->encodePassword($user, $user->getPassword()));
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(
            $user,
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('api_user_show', ['id' => $user->getId()])
            ],
            ['groups' => 'user']
        );
    }

    /**
     * @Route("/api/user/{id<\d+>}", name="api_user_put_and_patch", methods={"PUT", "PATCH"})
     */
    public function putAndPatch(User $user = null, Request $request, SerializerInterface $serializer, MyValidator $validator, UserPasswordEncoderInterface $userPasswordEncoder, EntityManagerInterface $entityManager): Response
    {
        if (!$user) {
            return $this->json(['error' => 'user not found'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('edit', $user);

        $serializer->deserialize($request->getContent(), User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);

        $data = json_decode($request->getContent(), true);
        if (isset($data['newPassword']) && isset($data['oldPassword'])) {
            if (!$userPasswordEncoder->isPasswordValid($user, $data['oldPassword'])) {
                return $this->json('wrong password', Response::HTTP_UNAUTHORIZED);
            }
            $user->setPassword($data['newPassword']);

            $errors = $validator->validate($user, null, 'register');
        }
        else {
            $errors = $validator->validate($user, null, 'update');
        }

        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (isset($data['newPassword']) && isset($data['oldPassword'])) {
            $user->setPassword($userPasswordEncoder->encodePassword($user, $user->getPassword()));
        }

        $entityManager->flush();

        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user']);
    }
}
