<?php

namespace App\Presenters;

use App;
use App\Model\Repository\Faculties;
use App\Forms\FacultiesFormsFactory;

/**
 * Faculties presenter.
 */
class FacultiesPresenter extends BasePresenter
{
    /**
     * @var Faculties
     * @inject
     */
    public $faculties;

    /**
     * @var FacultiesFormsFactory
     * @inject
     */
    public $facultiesFormsFactory;

    public function createComponentAddFaculty()
    {
        $form = $this->facultiesFormsFactory->createAddFacultyForm();
        $form->onSuccess[] = function (App\Forms\MyForm $form, $values) {
            $form->presenter->flashMessage('Faculty was successfully added.');
            $form->presenter->redirect('Faculties:');
        };
        return $form;
    }

    public function createEditFaculty($faculty)
    {
        $form = $this->facultiesFormsFactory->createEditFacultyForm($faculty);
        $form->setDefaults($faculty->toArray());
        $form->onSuccess[] = function (App\Forms\MyForm $form, $values) {
            $form->presenter->flashMessage('Faculty was successfully edited.');
            $form->presenter->redirect('Faculties:');
        };
        return $form;
    }

    public function actionDefault()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Faculties", "management")) {
            $this->error("Access Denied");
        }

        $this->template->faculties = $this->faculties->findAll();
    }

    public function actionAddFaculty()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Faculties", "management")) {
            $this->error("Access Denied");
        }
    }

    public function actionEditFaculty($id)
    {
        $faculty = $this->faculties->findOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Faculties", "management")) {
            $this->error("Access Denied");
        }

        $this['editFaculty'] = $this->createEditFaculty($faculty);
    }
}
