<?php

namespace App\Users;

use App\Model\Entity\LoginLog;
use App\Model\Repository\Users;
use App\Model\Repository\LoginLogs;
use Nette;
use Nette\Security\Passwords;

/**
 * Simple doctrine authenticator through users repository, every user login is
 * also logged into login log.
 */
class MyAuthenticator extends Nette\Object implements Nette\Security\IAuthenticator
{
    /** @var Users */
    private $users;
    /** @var LoginLogs */
    private $loginLogs;
    /** @var Nette\Http\IRequest */
    private $httpRequest;

    /**
     * DI Constructor.
     * @param Users $users
     * @param LoginLogs $loginLogs
     * @param Nette\Http\IRequest $httpRequest
     */
    public function __construct(
        Users $users,
        LoginLogs $loginLogs,
        Nette\Http\IRequest $httpRequest
    ) {
        $this->users = $users;
        $this->loginLogs = $loginLogs;
        $this->httpRequest = $httpRequest;
    }

    /**
     * Performs an authentication.
     * @return Nette\Security\IIdentity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;
        $user = $this->users->findByUsername($username);

        if (!$user) {
            throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
        } elseif (!Passwords::verify($password, $user->password)) {
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
        } elseif (Passwords::needsRehash($user->password)) {
            $user->password = $password;
            $this->users->flush();
        }

        // everything went fine, just stalk users a bit
        $loginLog = new LoginLog($user, $this->httpRequest->getRemoteAddress(), $this->httpRequest->getHeader('User-Agent'));
        $this->loginLogs->persist($loginLog);

        // ... and return new identity of user
        return $user;
    }
}
