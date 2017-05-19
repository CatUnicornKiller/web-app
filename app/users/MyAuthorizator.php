<?php

namespace App\Users;

use App;
use App\Users\UserManager;
use App\Model\Entity\User;
use App\Model\Entity\Event;

/**
 * Very simple authorizator for the cases when the integrated one is not
 * flexible enough.
 * @note Desperately needs refactoring!
 */
class MyAuthorizator
{
    /** @var User */
    private $user;
    private $facultyId;
    private $role;
    private $userType;
    private $roleInt;
    /** @var App\Users\RolesManager */
    private $rolesManager;

    /**
     * DI Constructor.
     * @param UserManager $userManager
     * @param App\Users\RolesManager $rolesManager
     */
    public function __construct(
        UserManager $userManager,
        App\Users\RolesManager $rolesManager
    ) {
        $this->user = $user = $userManager->getCurrentUser();
        if ($user) {
            $this->facultyId = $user->faculty->id;
            $this->role = $user->role;
            $this->userType = $user->userType;
        }
        $this->rolesManager = $rolesManager;
        $this->roleInt = $rolesManager->roleToInt($this->role);
    }

    /**
     * AssignAf operations authorizator.
     * @param string $privilege
     * @param int $facultyId
     * @return boolean true if allowed
     */
    public function isAllowedAssignAf($privilege, $facultyId)
    {
        if ($privilege == 'delete') {
            if ($facultyId == $this->facultyId || $this->roleInt >= 110) {
                return true;
            }
        }

        return false;
    }

    /**
     * Event operations authorizator.
     * @param string $privilege
     * @param Event $event
     * @return boolean true if allowed
     */
    public function isAllowedEvent($privilege, Event $event)
    {
        if ($this->role == 'admin') {
            return true;
        }

        if ($privilege == 'view') {
            if ($event->isVisibleToFaculty($this->user->faculty) ||
                    $this->roleInt >= 110) {
                return true;
            }
        } elseif ($privilege == 'signUp' || $privilege == 'unSign') {
            if ($event->isVisibleToFaculty($this->user->faculty)) {
                return true;
            }
        } elseif ($privilege == 'addCoorg' || $privilege == 'delCoorg') {
            if ($this->user->id == $event->user->id || $this->roleInt >= 110) {
                return true;
            }
        } elseif ($privilege == 'edit' || $privilege == 'delete' ||
                $privilege == 'generateParticipants') {
            if (($this->user->id == $event->user->id) || ($this->roleInt >= 110) ||
                    ($this->roleInt >= 100 && $this->facultyId == $event->user->faculty->id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Incomings operations authorizator.
     * @param string $privilege
     * @param int $userId
     * @param int $facultyId
     * @return boolean true if allowed
     */
    public function isAllowedIncomings($privilege, $userId, $facultyId)
    {
        if ($privilege == 'changeRole' || $privilege == 'delete') {
            if ($this->roleInt >= 110 || ($this->facultyId == $facultyId)) {
                return true;
            }
        } elseif ($privilege == 'view') {
            if (($this->userType == 'incoming' && $userId == $this->user->id) ||
                    ($this->userType == 'officer' &&
                        ($this->facultyId == $facultyId || $this->roleInt >= 110))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Users operations authorizator.
     * @param string $privilege
     * @param int $userId
     * @param int $facultyId
     * @param string $role
     * @return boolean true if allowed
     */
    public function isAllowedUsers($privilege, $userId, $facultyId, $role)
    {
        if ($privilege == 'view') {
            if ($facultyId == $this->facultyId || $this->roleInt >= 110) {
                return true;
            }
        } elseif ($privilege == 'changeRole' || $privilege == 'changeIfmsa') {
            if ($this->user->id == $userId ||
                    ($this->roleInt >= 110 && $this->roleInt > $this->rolesManager->roleToInt($role)) ||
                    ($this->roleInt < 110 && $this->facultyId == $facultyId &&
                        $this->roleInt > $this->rolesManager->roleToInt($role))) {
                return true;
            }
        } elseif ($privilege == 'delete') {
            if ($this->roleInt >= 120 || $this->facultyId == $facultyId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tasks operations authorizator.
     * @param string $privilege
     * @param int $facultyId
     * @return boolean true if allowed
     */
    public function isAllowedTasks($privilege, $facultyId)
    {
        if ($privilege == 'view' || $privilege == 'edit' ||
                $privilege == 'add' || $privilege == 'delete') {
            if ($facultyId == $this->facultyId || $this->roleInt >= 110) {
                return true;
            }
        }

        return false;
    }

    /**
     * IncomingsFacultyInformation operations authorizator.
     * @param string $privilege
     * @param int $facultyId
     * @return boolean true if allowed
     */
    public function isAllowedIncomingsFacultyInformation($privilege, $facultyId)
    {
        if ($privilege == 'view') {
            if ($this->roleInt >= 110 || $this->facultyId == $facultyId) {
                return true;
            }
        } elseif ($privilege == 'edit') {
            if ($this->facultyId == $facultyId || !$facultyId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if currently logged user is from SCOPE.
     * @return boolean
     */
    public function isScope()
    {
        if ($this->role == "nore" || $this->role == "lore" ||
                $this->role == "lore_assist") {
            return false;
        }

        return true;
    }
}
