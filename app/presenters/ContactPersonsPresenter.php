<?php

namespace App\Presenters;

use App;
use App\Model\Repository\Pages;
use App\Model\Repository\CpAssignedAfs;
use App\Model\Repository\IfmsaPersons;

/**
 * Contact Persons presenter.
 */
class ContactPersonsPresenter extends BasePresenter
{
    /**
     * @var App\Forms\PagesFormsFactory
     * @inject
     */
    public $pagesFormsFactory;
    /**
     * @var App\Forms\ContactPersonsFormsFactory
     * @inject
     */
    public $contactPersonsFormsFactory;
    /**
     * @var App\Helpers\IfmsaConnectionHelper
     * @inject
     */
    public $ifmsaConnectionHelper;
    /**
     * @var Pages
     * @inject
     */
    public $pages;
    /**
     * @var CpAssignedAfs
     * @inject
     */
    public $cpAssignedAfs;
    /**
     * @var IfmsaPersons
     * @inject
     */
    public $ifmsaPersons;

    protected function createComponentAfAssignForm()
    {
        $form = $this->contactPersonsFormsFactory->createAfAssignForm();
        $form->onSuccess[] = function (App\Forms\MyForm $form, $values) {
            $form->presenter->flashMessage('Incoming was successfully assigned.');
            $form->presenter->redirect('Officers:profile', $values->userId);
        };
        return $form;
    }

    protected function createComponentAfRefreshForm()
    {
        $form = $this->contactPersonsFormsFactory->createAfRefreshForm();
        $form->onSuccess[] = function (App\Forms\MyForm $form, $values) {
            $form->presenter->flashMessage('Incoming information was successfully refreshed.');
            $form->presenter->redirect('Officers:profile', $values->userId);
        };
        return $form;
    }

    public function actionIntroduction()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed('CPIntroduction', 'view')) {
            $this->error("Access Denied");
        }

        $this->template->page = $this->pages->getPage('cp_introduction', 'default');
    }

    public function actionEditIntroduction()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed('CPIntroduction', 'edit')) {
            $this->error("Access Denied");
        }

        $page = $this->pages->getPage('cp_introduction', 'default');

        $form = $this->pagesFormsFactory->createPagesForm($page);
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('Content successfully changed.');
            $form->getPresenter()->redirect('ContactPersons:introduction');
        };
        $this['pagesForm'] = $form;
    }

    public function actionProfile()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed('CPProfile', 'view')) {
            $this->error("Access Denied");
        }

        $this->template->profile = $this->currentUser;
    }

    public function actionAfFindContactPerson($afNumber)
    {
        if (!$this->isLoggedIn() || !$afNumber ||
                !$this->user->isAllowed('AssignAf', 'view')) {
            $this->error("Access Denied");
        }

        $form = $this->contactPersonsFormsFactory->createFindContactPersonForm($afNumber);
        $form->setAction($this->link('ContactPersons:afAssign'));
        $this['findContactPersonForm'] = $form;
    }

    public function actionAfAssign()
    {
        $afNumber = $this->request->getPost('afNumber');
        $userId = $this->request->getPost('userId');

        if (!$this->isLoggedIn() || !$this->request->isMethod('post') ||
                !$this->user->isAllowed('AssignAf', 'view') ||
                !$afNumber || !$userId) {
            $this->error("Access Denied");
        }
    }

    public function renderAfAssign()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed('AssignAf', 'view')) {
            $this->error("Access Denied");
        }

        $afNumber = $this->request->getPost('afNumber');
        $userId = $this->request->getPost('userId');
        $person = $this->ifmsaPersons->findByAfNumber($afNumber);
        $personInfo = array();
        try {
            $this->ifmsaConnectionHelper->fetchPersonAf($afNumber, $personInfo);
            $this->ifmsaConnectionHelper->fetchPersonCC($person->confirmationNumber, $personInfo);
        } catch (\App\Exceptions\IfmsaConnectionException $e) {
            $msg = 'Ifmsa.org connection error: ' . $e->getMessage();
            $this->redirect("Ifmsa:connectionError", $msg);
        }

        // and fill appropriate form
        $this->contactPersonsFormsFactory->fillAfForm(
            $this['afAssignForm'],
            $afNumber,
            $userId,
            $personInfo
        );
    }

    public function actionAfRefresh($afNumber, $userId)
    {
        if (!$this->isLoggedIn() || !$afNumber || !$userId ||
                !$this->user->isAllowed('AssignAf', 'view')) {
            $this->error("Access Denied");
        }
    }

    public function renderAfRefresh($afNumber, $userId)
    {
        $personInfo = array();
        $person = $this->ifmsaPersons->findByAfNumber($afNumber);
        try {
            $this->ifmsaConnectionHelper->fetchPersonAf($afNumber, $personInfo);
            $this->ifmsaConnectionHelper->fetchPersonCC($person->confirmationNumber, $personInfo);
        } catch (\App\Exceptions\IfmsaConnectionException $e) {
            $msg = 'Ifmsa.org connection error: ' . $e->getMessage();
            $this->redirect("Ifmsa:connectionError", $msg);
        }

        // and fill appropriate form
        $this->contactPersonsFormsFactory->fillAfForm(
            $this['afRefreshForm'],
            $afNumber,
            $userId,
            $personInfo
        );
    }

    public function actionAfDelete($afNumber, $userId)
    {
        $user = $this->users->findOfficerOrThrow($userId);
        if (!$this->isLoggedIn() || !$afNumber ||
                !$this->user->isAllowed('AssignAf', 'view')) {
            $this->error("Access Denied");
        }

        $assign = $user->getAssignedIncoming($afNumber);
        if ($assign && $this->myAuthorizator->isAllowedAssignAf('delete', $user->faculty->id)) {
            $assign->modified($this->currentUser);
            $assign->delete();
            $this->cpAssignedAfs->flush();

            $this->flashMessage('Incoming was successfully unassigned');
        }
        $this->redirect('Officers:profile', $userId);
    }
}
