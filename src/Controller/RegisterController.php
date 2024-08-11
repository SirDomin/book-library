<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/api/register', name: 'api_registration', methods: ['POST'])]
    public function register(
        Request $request,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));

        $errors = $this->validator->validate($user);

        $errorMessages = [];

        if (0 === count($errors)) {
            try {
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $this->json(['message' => 'User registered successfully'], Response::HTTP_CREATED);
            } catch (ConstraintViolationException $constraintViolationException) {
                $errorMessages[] = ['message' => 'Username with given email or login already exists'];
            }
        }

        foreach ($errors as $error) {
            $errorMessages[] = [
                'message' => $error->getMessage(),
                'property' => $error->getPropertyPath(),
                'value' => $error->getInvalidValue(),
            ];
        }

        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }
}
