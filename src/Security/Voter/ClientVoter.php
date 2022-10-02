<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Client;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ClientVoter extends Voter
{
    public const DELETE = 'DELETE_USER';
    public const VIEW = 'VIEW_USER';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::DELETE, self::VIEW])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $client = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$client instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            SELF::DELETE => $this->canDelete($subject, $client),
            SELF::VIEW => $this->canView($subject, $client),
            default =>  false
        };
    }

    private function canView(User $user, Client $client): bool
    {
        if ($user->getClient() !== $client) {
            return false;
        }
        return true;
    }

    private function canDelete(User $user, Client $client): bool
    {
        if ($user->getClient() !== $client) {
            return false;
        }
        return true;
    }
}
