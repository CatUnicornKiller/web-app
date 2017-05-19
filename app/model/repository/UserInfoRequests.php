<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityManager;
use App\Model\Entity\UserInfoRequest;

/**
 * Repository of operations performed on UserInfoRequest entities.
 */
class UserInfoRequests extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, UserInfoRequest::class);
    }
}
