<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\MyValidator;
use App\Service\FileUploader;
use App\Service\ValidatorError;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
     * @Route("/api/user", name="api_user_list", methods={"GET"})
     */
    public function listBySetlist(Request $request, EventRepository $eventRepository, SerializerInterface $serializer): Response
    {
        $setlistId = trim($request->query->get('setlistId', ''));

        $event = $eventRepository->findOneBy(['setlistId' => $setlistId]);
        if ($event) {
            $users = $event->getUsers();
        } else {
            $users = [];
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

    /**
     * @Route("/api/user/avatar/{id<\d+>}", name="api_user_avatar_add", methods={"POST"})
     */
    public function add(User $user = null, Request $request, ValidatorError $validatorError, FileUploader $fileUploader, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        if (!$user) {
            return $this->json(['error' => 'user not found'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('edit', $user);

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

        $user->setAvatar($fileUploader->upload($form->get('image')->getData(), 'avatars'));
        $entityManager->flush();

        return $this->json($user->getAvatar(), Response::HTTP_CREATED);
    }
}
