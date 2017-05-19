<?php

namespace App\Presenters;

use Nette;
use App;
use App\Model\Entity\User;
use App\Model\Repository\LoginLogs;
use App\Model\Repository\UserInfoRequests;
use App\Model\Entity\UserInfoRequest;
use App\Model\Repository\ExtraPointsRepository;

/**
 * Users presenter.
 */
class OfficersPresenter extends BasePresenter
{
    /**
     * @var App\Forms\UsersFormsFactory
     * @inject
     */
    public $usersFormsFactory;
    /**
     * @var App\Helpers\UserHelper
     * @inject
     */
    public $userHelpers;
    /**
     * @var LoginLogs
     * @inject
     */
    public $loginLogs;
    /**
     * @var UserInfoRequests
     * @inject
     */
    public $userInfoRequests;
    /**
     * @var ExtraPointsRepository
     * @inject
     */
    public $extraPointsRepository;

    public function createModifyEventsPointsForm($id)
    {
        $form = $this->usersFormsFactory->createModifyEventsPointsForm($id);
        $form->onSuccess[] = function (App\Forms\MyForm $form, $values) {
            $form->presenter->flashMessage('Points were successfully changed');
            $form->presenter->redirect('Officers:profile', $values->id);
        };
        return $form;
    }

    public function createModifyCoorgEventsPointsForm($id)
    {
        $form = $this->usersFormsFactory->createModifyCoorgEventsPointsForm($id);
        $form->onSuccess[] = function (App\Forms\MyForm $form, $values) {
            $form->presenter->flashMessage('Coorganization Points were successfully changed');
            $form->presenter->redirect('Officers:profile', $values->id);
        };
        return $form;
    }

    protected function createComponentChangeRoleForm($id)
    {
        $form = $this->usersFormsFactory->createchangeRoleForm(
            $id,
            $this->rolesManager->getRolesDescription()
        );
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('Role succesfully changed.');
            $this->redirect('Officers:profile', $form->values->id);
        };
        return $form;
    }

    protected function createComponentModifyIfmsaCredentialsForm($id)
    {
        $form = $this->usersFormsFactory->createModifyIfmsaCredentialsForm($id);
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('Ifmsa credentials succesfully changed.');
            $this->redirect('Officers:profile', $form->values->id);
        };
        return $form;
    }

    protected function createFilterOfficersForm($faculty, $privileges)
    {
        $form = $this->usersFormsFactory->createFilterOfficersForm($faculty, $privileges);
        $form->elementPrototype->addClass('ajax');
        return $form;
    }

    protected function createAddExtraPointsForm(User $user)
    {
        $form = $this->usersFormsFactory->createAddExtraPointsForm($user);
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('Extra Points successfully added.');
            $this->redirect('Officers:profile', $form->values->id);
        };
        return $form;
    }

    public function actionDefault($page, $orderby, $order, $faculty, $privileges)
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("UsersList", "view")) {
            $this->error("Access Denied");
        }

        // get data from database and engage pagination
        $query = $this->users->getOfficersListQuery($orderby, $order, $faculty, $privileges);
        $paginator = $this->paginate($query, $page);

        $this->template->users = $paginator->getIterator();
        $this['filterUsersForm'] = $this->createFilterOfficersForm($faculty, $privileges);

        if ($this->isAjax()) {
            $this->redrawControl('officersList');
        }
    }

    public function actionProfile($id)
    {
        $profile = $this->users->findOfficerOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Users", "view") ||
                !$this->myAuthorizator->isAllowedUsers(
                    'view',
                    $profile->id,
                    $profile->faculty->id,
                    $profile->role
                )) {
            $this->error('Access Denied');
        }

        $this->template->profile = $profile;
        $this->template->officerProfile = $profile->officersProfile;
        $this->template->canRequest =
                $this->userHelpers->canRequestAdditionalInfo($profile->infoRequests->count(), $profile->officersProfile);
        $this->template->canUpload = $this->userHelpers->isAdditionalInfoFilled($profile->officersProfile);
        $this->template->canDelete = $this->user->isAllowed('Users', 'delete') &&
                $this->myAuthorizator->isAllowedUsers('delete', $profile->id, $profile->faculty->id, $profile->role);
        $this->template->canChangeIfmsa = $this->user->isAllowed('Users', 'changeIfmsa') &&
                $this->myAuthorizator->isAllowedUsers('changeIfmsa', $profile->id, $profile->faculty->id, $profile->role);
        $this->template->canChangeRole =
                $this->myAuthorizator->isAllowedUsers('changeRole', $profile->id, $profile->faculty->id, $profile->role);

        $this['modifyEventsPoints'] = $this->createModifyEventsPointsForm($id);
        $this['modifyCoorgEventsPoints'] = $this->createModifyCoorgEventsPointsForm($id);
    }

    public function actionRole($id)
    {
        $usr = $this->users->findOfficerOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Users", "changeRole") ||
                !$this->myAuthorizator->isAllowedUsers('changeRole', $usr->id, $usr->faculty->id, $usr->role)) {
            $this->error("Access Denied");
        }

        $this['changeRoleForm'] = $this->createComponentChangeRoleForm($id);
        try {
            $this['changeRoleForm']->setDefaults(array('role' => $usr->role));
        } catch (Nette\InvalidArgumentException $e) {
        }
    }

    public function actionDeleteOfficer($id)
    {
        $usr = $this->users->findOfficerOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Users", "delete") ||
                !$this->myAuthorizator->isAllowedUsers('delete', $usr->id, $usr->faculty->id, $usr->role)) {
            $this->error("Access Denied");
        }

        $usr->modified($this->currentUser);
        $usr->delete();
        $this->users->flush();

        $this->flashMessage('Officer was successfully deleted.');
        $this->redirect('Officers:');
    }

    public function actionLoginLog()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("UsersList", "view")) {
            $this->error("Access Denied");
        }

        $this->template->loginLog = $this->loginLogs->getLastOfficersLogins();
    }

    public function actionModifyIfmsaCredentials($id)
    {
        $usr = $this->users->findOfficerOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Users", "changeIfmsa") ||
                !$this->myAuthorizator->isAllowedUsers('changeIfmsa', $usr->id, $usr->faculty->id, $usr->role)) {
            $this->error("Access Denied");
        }

        $this['modifyIfmsaCredentialsForm'] =
                $this->createComponentModifyIfmsaCredentialsForm($id);
    }

    public function actionRequestAdditionalInfo($id)
    {
        $usr = $this->users->findOfficerOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Users", "view") ||
                !$this->myAuthorizator->isAllowedUsers('view', $usr->id, $usr->faculty->id, $usr->role)) {
            $this->error('Access Denied');
        }

        $requests = $usr->getAdditionalInfoRequests();
        if (count($requests) > 0) {
            $this->error('Too many active additional info requests');
        }

        $request = UserInfoRequest::requestAdditionalInfo($this->currentUser, $usr);
        $this->userInfoRequests->persist($request);

        $this->flashMessage('Request was successfully sent to user.');
        $this->redirect('Officers:profile', $id);
    }

    public function actionDeleteAdditionalInfoRequest($id)
    {
        $request = $this->userInfoRequests->findOfficerOrThrow($id);
        $usr = $this->users->findOrThrow($request->requestedUser);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Users", "view") ||
                !$this->myAuthorizator->isAllowedUsers('view', $usr->id, $usr->faculty->id, $usr->role)) {
            $this->error('Access Denied');
        }

        $request->delete();
        $request->deletedByUser = $this->currentUser;
        $request->completed = false;
        $request->deletedTime = new \DateTime;
        $this->userInfoRequests->flush();

        $this->flashMessage('Additional Info request was successfully deleted.');
        $this->redirect('Officers:profile', $usr->id);
    }

    public function actionAddExtraPoints($id)
    {
        $user = $this->users->findOfficerOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Users", "view") ||
                !$this->myAuthorizator->isAllowedUsers('view', $user->id, $user->faculty->id, $user->role)) {
            $this->error('Access Denied');
        }

        $this['addExtraPointsForm'] = $this->createAddExtraPointsForm($user);
    }

    public function actionDeleteExtraPoints($id)
    {
        $points = $this->extraPointsRepository->findOrThrow($id);
        $user = $this->users->findOfficerOrThrow($points->user);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Users", "view") ||
                !$this->myAuthorizator->isAllowedUsers('view', $user->id, $user->faculty->id, $user->role)) {
            $this->error('Access Denied');
        }

        $points->modified($this->currentUser);
        $points->delete();
        $this->extraPointsRepository->flush();

        $this->flashMessage('Extra Points were successfully deleted.');
        $this->redirect('Officers:profile', $user->id);
    }
}
