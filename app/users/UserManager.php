<?php

namespace App\Users;

use App\Model\Entity\User;
use App\Model\Repository\Users;
use Nette;

/**
 * Users management, basically cache for the current user or any other needed
 * operation.
 */
class UserManager extends Nette\Object
{
    /** @var Nette\Security\User */
    private $user;
    /** @var User */
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
     * @return User|NULL
     */
    public function getCurrentUser()
    {
        if (!$this->cachedUser) {
            $this->cachedUser = $this->users->get($this->user->id);
        }
        return $this->cachedUser;
    }
}
