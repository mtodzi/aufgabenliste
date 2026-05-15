<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isActive()) {
            throw new CustomUserMessageAuthenticationException(
                'Ваша учетная запись деактивирована.'
            );
        }

        $organization = $user->getOrganization();

        // Если у пользователя есть организация и она не активна — блокируем вход
        // Системные администраторы без организации (null) проходят проверку
        if ($organization !== null && !$organization->isActive()) {
            throw new CustomUserMessageAuthenticationException(
                'Ваша организация деактивирована. Пожалуйста, свяжитесь с администратором системы.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        // Здесь можно добавить проверки после успешной авторизации, если нужно
    }
}