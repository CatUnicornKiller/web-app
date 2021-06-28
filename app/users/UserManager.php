<?php

namespace App\Users;

use App\Model\Entity\User;
use App\Model\Repository\Users;
use Nette;

/**
 * Users management, basically cache for the current user or any other needed
 * operation.
 */
class UserManager
{
    use Nette\SmartObject;

    /** @var Nette\Security\User */
    private $user;
    /** @var User|null */
    private $cachedUser;
    /** @var Users */
    private $users;

    /**
     * DI Constructor.
     * @param Nette\Security\User $user
     * @param Users $users
     */
    public function __construct(
        Nette\Security\User $user,
        Users $users
    ) {
        $this->user = $user;
        $this->users = $users;
    }

    /**
     * Get currently logged user.
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        if (!$this->user->isLoggedIn()) {
            // user is not logged in, return null
            return null;
        }

        if (!$this->cachedUser) {
            // user is not cached, load it from database
            $this->cachedUser = $this->users->get($this->user->id);
        }
        return $this->cachedUser;
    }
}
