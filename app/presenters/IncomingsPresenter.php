<?php

namespace App\Presenters;

use App;
use App\Model\Repository\LoginLogs;
use App\Model\Repository\Pages;
use Nette;

/**
 * Incomings presenter.
 */
class IncomingsPresenter extends BasePresenter
{
    /**
     * @var App\Forms\PagesFormsFactory
     * @inject
     */
    public $pagesFormsFactory;
    /**
     * @var App\Forms\IncomingsFormsFactory
     * @inject
     */
    public $incomingsFormsFactory;
    /**
     * @var LoginLogs
     * @inject
     */
    public $loginLogs;
    /**
     * @var Pages
     * @inject
     */
    public $pages;

    protected function createFilterIncomingsForm($faculty, $privileges)
    {
        $form = $this->incomingsFormsFactory->createFilterIncomingsForm($faculty, $privileges);
        $form->elementPrototype->addClass('ajax');
        return $form;
    }

    protected function createChangeRoleForm($usr)
    {
        $form = $this->incomingsFormsFactory->createChangeRoleForm(
            $usr->id,
            $this->rolesManager->getIncomingsRolesDescription()
        );
        try {
            $form->setDefaults(array('role' => $usr->role));
        } catch (Nette\InvalidArgumentException $e) {
        }

        $form->onSuccess[] = function ($form) {
            $this->flashMessage('Role succesfully changed.');
            $this->redirect('Incomings:profile', $form->values->id);
        };

        return $form;
    }

    public function actionDefault($page, $orderby, $order, $faculty, $privileges)
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("IncomingsList", "view")) {
            $this->error("Access Denied");
        }

        // get data from database and engage pagination
        $query = $this->users->getIncomingsListQuery($orderby, $order, $faculty, $privileges);
        $paginator = $this->paginate($query, $page);

        $this->template->incomings = $paginator->getIterator();
        $this['filterUsersForm'] = $this->createFilterIncomingsForm($faculty, $privileges);

        if ($this->isAjax()) {
            $this->redrawControl('incomingsList');
        }
    }

    public function actionLoginLog()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("IncomingsList", "view")) {
            $this->error("Access Denied");
        }

        $this->template->loginLog = $this->loginLogs->getLastIncomingsLogins();
    }

    public function actionFacultyInformation($facultyId)
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed('IncomingsFacultyInformation', 'view') ||
                ($facultyId && !$this->myAuthorizator->isAllowedIncomingsFacultyInformation('view', $facultyId))) {
            $this->error("Access Denied");
        }

        if (!$facultyId) {
            $facultyId = $this->currentUser->faculty->id;
        }

        $this->template->pageDefault = $page = $this->pages->getPage('incomings_fac_info', 'default', $facultyId);
        $this->template->canEdit = $this->user->isAllowed('IncomingsFacultyInformation', 'edit') &&
                $this->myAuthorizator->isAllowedIncomingsFacultyInformation('edit', $facultyId);
    }

    public function actionEditFacultyInformation()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed('IncomingsFacultyInformation', 'edit')) {
            $this->error("Access Denied");
        }

        $page = $this->pages->getPage('incomings_fac_info', 'default', $this->currentUser->faculty->id);

        $form = $this->pagesFormsFactory->createPagesForm($page, $this->currentUser->faculty->id);
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('Content successfully changed.');
            $form->getPresenter()->redirect('Incomings:facultyInformation');
        };
        $this['pagesForm'] = $form;
    }

    public function actionFacultyInformationList()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed('IncomingsFacultyInformationList', 'view')) {
            $this->error("Access Denied");
        }

        $this->template->pages = $this->pages->findByNameAndSubname('incomings_fac_info', 'default');
    }

    public function actionGeneralInformation()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed('IncomingsGeneralInformation', 'view')) {
            $this->error("Access Denied");
        }

        $this->template->pageDefault = $this->pages->getPage('incomings_gen_info', 'default');
        $this->template->pageBefore = $this->pages->getPage('incomings_gen_info', 'before_clerkship');
        $this->template->pageDuring = $this->pages->getPage('incomings_gen_info', 'during_clerkship');
        $this->template->pageAfter = $this->pages->getPage('incomings_gen_info', 'after_clerkship');
    }

    public function actionEditGeneralInformation($subpage)
    {
        if (!$this->isLoggedIn() || !$subpage ||
                !$this->user->isAllowed('IncomingsGeneralInformation', 'edit')) {
            $this->error("Access Denied");
        }

        $page = $this->pages->getPage('incomings_gen_info', $subpage);

        $form = $this->pagesFormsFactory->createPagesForm($page);
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('Content successfully changed.');
            $form->getPresenter()->redirect('Incomings:generalInformation');
        };
        $this['pagesForm'] = $form;
    }

    public function actionProfile($id)
    {
        if (!$id) {
            $id = $this->user->id;
        }
        $profile = $this->users->findIncomingOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Incomings", "view") ||
                !$this->myAuthorizator->isAllowedIncomings('view', $profile->id, $profile->faculty->id)) {
            $this->error('Access Denied');
        }

        $this->template->profile = $profile;
        $this->template->signedUpEvents = $profile->participatedEvents;
        $this->template->canDelete = $this->user->isAllowed('Incomings', 'delete') &&
                $this->myAuthorizator->isAllowedIncomings('delete', $profile->id, $profile->faculty->id);
        $this->template->canChangeRole = $this->user->isAllowed('Incomings', 'changeRole') &&
                $this->myAuthorizator->isAllowedIncomings('changeRole', $profile->id, $profile->faculty->id);
    }

    public function actionDeleteIncoming($id)
    {
        $usr = $this->users->findIncomingOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Incomings", "delete") ||
                !$this->myAuthorizator->isAllowedIncomings('delete', $usr->id, $usr->faculty->id)) {
            $this->error("Access Denied");
        }

        $usr->modified($this->currentUser);
        $usr->delete();
        $this->users->flush();

        $this->flashMessage('Incoming was successfully deleted.');
        $this->redirect('Incomings:');
    }

    public function actionIfmsaHierarchy()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("IncomingsIfmsaHierarchy", "view")) {
            $this->error("Access Denied");
        }

        $officers = array();
        $officers['NEOs'] = $this->users->findByRole("neo");
        $officers['NOREs'] = $this->users->findByRole("nore");
        $officers['NEO Assistants'] = $this->users->findByRole("neo_assist");
        $officers['LEOs'] = $this->users->findByRole("leo");
        $officers['LOREs'] = $this->users->findByRole("lore");
        $this->template->officers = $officers;
    }

    public function actionChangeRole($id)
    {
        $usr = $this->users->findIncomingOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Incomings", "changeRole") ||
                !$this->myAuthorizator->isAllowedIncomings('changeRole', $usr->id, $usr->faculty->id)) {
            $this->error("Access Denied");
        }

        $this['changeRoleForm'] = $this->createChangeRoleForm($usr);
    }
}
