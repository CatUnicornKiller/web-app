<?php

namespace App\Presenters;

use App\Forms\UserFormsFactory;

/**
 * User presenter.
 */
class UserPresenter extends BasePresenter
{
    /**
     * @var UserFormsFactory
     * @inject
     */
    public $userFormsFactory;

    protected function createComponentChangePasswdForm()
    {
        $form = $this->userFormsFactory->createChangePasswdForm();
        $form->onSuccess[] = function () {
            $this->flashMessage('Password succesfully changed.');
            $this->redirect('User:');
        };
        return $form;
    }

    protected function createComponentEditUserForm()
    {
        $form = $this->userFormsFactory->createEditUserForm();
        $form->onSuccess[] = function () {
            $this->flashMessage('User information succesfully changed.');
            $this->redirect('User:');
        };
        return $form;
    }

    public function actionDefault(array $requested)
    {
        if (!$this->isLoggedIn()) {
            $this->error("Access Denied");
        }

        $this->template->userProfile = $this->currentUser;
        $this->template->requested = $requested;
    }

    public function actionPasswd()
    {
        if (!$this->isLoggedIn()) {
            $this->error("Access Denied");
        }
    }

    public function actionDeleteProfileImage()
    {
        if (!$this->isLoggedIn()) {
            $this->error("Access Denied");
        }

        // delete image
        $this->currentUser->profileImg = "";
        $this->currentUser->modified($this->currentUser);
        $this->users->flush();

        $this->flashMessage("Profile image successfully deleted.");
        $this->redirect("User:");
    }
}
