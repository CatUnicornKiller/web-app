<?php

namespace App\Presenters;

use App;
use App\Model\Repository\ShowroomRepository;

/**
 * Showroom presenter.
 */
class ShowroomPresenter extends BasePresenter
{
    /**
     * @var ShowroomRepository
     * @inject
     */
    public $showroomRepository;
    /**
     * @var App\Forms\ShowroomFormsFactory
     * @inject
     */
    public $showroomFormsFactory;

    private function createAddOfficerForm($officer)
    {
        $form = $this->showroomFormsFactory->createAddOfficerForm($officer);
        $form->onSuccess[] = function ($form, $values) {
            $form->presenter->flashMessage($values->firstname . ' ' .
                    $values->surname . ' was successfully added to Showroom');
            $form->presenter->redirect("Showroom:list");
        };
        return $form;
    }

    private function createEditOfficerForm($officer)
    {
        $form = $this->showroomFormsFactory->createEditOfficerForm($officer);
        $form->onSuccess[] = function ($form, $values) {
            $form->presenter->flashMessage($values->firstname . ' ' .
                    $values->surname .
                    ' was successfully modified in Showroom');
            $form->presenter->redirect("Showroom:list");
        };
        return $form;
    }

    protected function startup()
    {
        parent::startup();
        $this->setLayout('layoutPublic');
    }

    public function renderDefault()
    {
        $officers = array();
        $officers['NEOs'] = $this->showroomRepository->findByRole("neo");
        $officers['NOREs'] = $this->showroomRepository->findByRole("nore");
        $officers['NEO Assistants'] = $this->showroomRepository->findByRole("neo_assist");
        $officers['LEOs'] = $this->showroomRepository->findByRole("leo");
        $officers['LOREs'] = $this->showroomRepository->findByRole("lore");
        $this->template->officers = $officers;
    }

    public function actionList()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Showroom", "management")) {
            $this->error('Access denied');
        }

        $officers = array();
        $officers['NEOs'] = $this->showroomRepository->findByRole("neo");
        $officers['NOREs'] = $this->showroomRepository->findByRole("nore");
        $officers['NEO Assistants'] = $this->showroomRepository->findByRole("neo_assist");
        $officers['LEOs'] = $this->showroomRepository->findByRole("leo");
        $officers['LOREs'] = $this->showroomRepository->findByRole("lore");
        $this->template->officers = $officers;

        // set private layout
        $this->setDefaultLayout();
    }

    public function actionOfficersList()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Showroom", "management")) {
            $this->error('Access denied');
        }

        $officers = array();
        $officers['NEOs'] = $this->users->findByRole("neo");
        $officers['NOREs'] = $this->users->findByRole("nore");
        $officers['NEO Assistants'] = $this->users->findByRole("neo_assist");
        $officers['LEOs'] = $this->users->findByRole("leo");
        $officers['LOREs'] = $this->users->findByRole("lore");
        $this->template->officers = $officers;

        // set private layout
        $this->setDefaultLayout();
    }

    public function actionAddOfficer($id)
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Showroom", "management")) {
            $this->error('Access denied');
        }

        $officer = null;
        if ($id) {
            $officer = $this->users->get($id);
        }

        $this['officerShowroomForm'] = $this->createAddOfficerForm($officer);

        $this->template->officerImg = "";
        if (!empty($officer->profileImg)) {
            $this->template->officerImg = $this->configParams->profileImgDir . $officer->profileImg;
        }

        // set private layout
        $this->setDefaultLayout();
    }

    public function actionEditOfficer($id)
    {
        if (!$this->isLoggedIn() || !$id ||
                !$this->user->isAllowed("Showroom", "management")) {
            $this->error('Access denied');
        }

        $officer = $this->showroomRepository->findOrThrow($id);
        $this['officerShowroomForm'] = $this->createEditOfficerForm($officer);

        $this->template->officerImg = "";
        if (!empty($officer->profileImg)) {
            $this->template->officerImg = $this->configParams->showroomImgDir . $officer->profileImg;
        }

        // set private layout
        $this->setDefaultLayout();
    }

    public function actionDeleteOfficer($id)
    {
        if (!$this->isLoggedIn() || !$id ||
                !$this->user->isAllowed("Showroom", "management")) {
            $this->error('Access denied');
        }

        $officer = $this->showroomRepository->findOrThrow($id);
        $this->showroomRepository->remove($officer);

        $this->flashMessage('Officer successfully deleted from Showroom');
        $this->redirect('Showroom:list');
    }
}
