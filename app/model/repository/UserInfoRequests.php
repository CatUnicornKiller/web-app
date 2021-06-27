<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\UserInfoRequest;

/**
 * Repository of operations performed on UserInfoRequest entities.
 */
class UserInfoRequests extends BaseRepository
{
    /**
     * DI Constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, UserInfoRequest::class);
    }
}
