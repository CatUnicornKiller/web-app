<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;
use App\Model\Entity\UserInfoRequest;

/**
 * Repository of operations performed on UserInfoRequest entities.
 *
 * @method UserInfoRequest|null get($id)
 * @method UserInfoRequest[] findAll()
 * @method UserInfoRequest[] findBy($params, $orderBy = [])
 * @method UserInfoRequest|null findOneBy($params)
 * @method UserInfoRequest findOrThrow($id)
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
