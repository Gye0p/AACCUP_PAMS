<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/users')]
#[IsGranted('ROLE_QUAMC_ADMIN')]
class AdminUserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('/{id}/reset-password', methods: ['POST'])]
    public function resetPassword(int $id, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found.');
        }

        $payload = $request->toArray();
        $newPassword = $payload['newPassword'] ?? null;
        if (!is_string($newPassword) || strlen($newPassword) < 8) {
            throw new BadRequestHttpException('The new password must be at least 8 characters.');
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
        $this->userRepository->save($user, true);

        return $this->json(['message' => 'Password reset successfully.']);
    }
}
