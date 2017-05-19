<?php

namespace App\Presenters;

use App;
use App\Forms\LoginFormFactory;
use App\Forms\RegistrationFormsFactory;
use App\Model\Repository\NewsRepository;
use App\Model\Repository\EventFiles;
use App\Model\Repository\LoginLogs;
use App\Model\Repository\Events;
use App\Model\Repository\EcommTransactions;
use App\Model\Repository\ForgottenPasswords;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    /**
     * @var LoginFormFactory
     * @inject
     */
    public $signFormFactory;
    /**
     * @var RegistrationFormsFactory
     * @inject
     */
    public $registrationFormsFactory;
    /**
     * @var App\Helpers\GuzzleFactory
     * @inject
     */
    public $guzzleFactory;
    /**
     * @var App\Forms\ForgottenPasswordFormsFactory
     * @inject
     */
    public $forgottenPasswordFormsFactory;
    /**
     * @var App\Forms\HomepageFormsFactory
     * @inject
     */
    public $homepageFormsFactory;
    /**
     * @var NewsRepository
     * @inject
     */
    public $newsRepository;
    /**
     * @var EventFiles
     * @inject
     */
    public $EventFiles;
    /**
     * @var LoginLogs
     * @inject
     */
    public $loginLogs;
    /**
     * @var Events
     * @inject
     */
    public $events;
    /**
     * @var ForgottenPasswords
     * @inject
     */
    public $forgottenPasswords;
    /**
     * @var EcommTransactions
     * @inject
     */
    public $ecommTransactions;

    protected function createComponentLoginForm()
    {
        $form = $this->signFormFactory->create();
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('You have been succesfully logged in.');
            $this->redirect('Homepage:dashboard');
        };
        return $form;
    }

    protected function createComponentRegistrationForm()
    {
        $form = $this->registrationFormsFactory->createOfficersForm();
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('You have been succesfully registrated.');
            $this->redirect('Homepage:default');
        };
        return $form;
    }

    protected function createComponentIncomingsRegistrationForm()
    {
        $form = $this->registrationFormsFactory->createIncomingsForm();
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('You have been succesfully registrated.');
            $this->redirect('Homepage:default');
        };
        return $form;
    }

    protected function createComponentForgottenPasswordForm()
    {
        $form = $this->forgottenPasswordFormsFactory->createForgottenPasswordForm();
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('Message with recovery link was sent to your email.');
            $this->redirect('Homepage:');
        };
        return $form;
    }

    protected function createRenewPasswordForm($ftoken)
    {
        $form = $this->forgottenPasswordFormsFactory->createRenewPasswordForm($ftoken);
        $form->onSuccess[] = function ($form) {
            $this->flashMessage('Password successfully changed.');
            $this->redirect('Homepage:');
        };
        return $form;
    }

    protected function createComponentAddNewsForm()
    {
        $form = $this->homepageFormsFactory->createAddNewsForm();
        $form->onSuccess[] = function (App\Forms\MyForm $form) {
            $this->flashMessage('News was successfully added');
            $this->redirect('Homepage:dashboard');
        };
        return $form;
    }

    public function actionDefault()
    {
        // nothing to do here
    }

    public function actionLogin()
    {
        if ($this->user->isLoggedIn()) {
            $this->redirect("Homepage:dashboard");
        }
    }

    public function actionDashboard()
    {
        if (!$this->isLoggedIn()) {
            $this->error("Access Denied");
        }

        if ($this->user->isAllowed('NewbyInfo', 'view')) {
            $this->flashMessage("Hey, I see you're new to CUK! Hang tight, officers have to authorize your access.");
        }

        $this->template->news = array();
        if ($this->user->isAllowed('News', 'view')) {
            $this->template->news = $this->newsRepository->getCurrentNews();
        }

        $infoRequests = $this->currentUser->infoRequests;
        $requestedInfoArr = array();
        $infoRequestBy = "";
        foreach ($infoRequests as $req) {
            $requestedInfoArr[] = $req->requestDesc;
            $infoRequestBy = $req->user->username;
        }

        $this->template->infoRequestBy = $infoRequestBy;
        $this->template->infoRequests = $infoRequests;
        $this->template->requestedInfoArr = array_unique($requestedInfoArr);

        if ($this->user->isAllowed('CUKStats', 'view')) {
            $this->template->randomEventImages = $this->EventFiles->getRandomFiles();
            $this->template->officersCount = $this->users->countOfficers();
            $this->template->incomingsCount = $this->users->countIncomings();
            $this->template->loginsCount = $this->loginLogs->countAll();
            $this->template->eventsCount = $this->events->countAll();
            $this->template->facultyEventsCount = $this->events->countEvents($this->currentUser->faculty);
            $this->template->transactionsCount = $this->ecommTransactions->countAll();
        }
    }

    public function actionFaq()
    {
        if (!$this->isLoggedIn()) {
            $this->error("Access Denied");
        }
    }

    public function actionLogout()
    {
        $this->user->logout();
        $this->guzzleFactory->destroySessionCookie();
        $this->flashMessage('You have been signed out.');
        $this->redirect('Homepage:');
    }

    public function actionRenew($ftoken)
    {
        if (!$ftoken) {
            $this->error('Token not given');
        }

        $token = $this->forgottenPasswords->findOneActiveByToken($ftoken);
        if ($token) {
            $this['renewPasswordForm'] = $this->createRenewPasswordForm($ftoken);
        } else {
            $this->error('Invalid token! This link is probably expired.');
        }
    }

    public function actionAddNews()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed('News', 'add')) {
            $this->error("Access Denied");
        }
    }

    public function actionDeleteNews($id)
    {
        $news = $this->newsRepository->findOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed('News', 'delete')) {
            $this->error("Access Denied");
        }

        $news->modified($this->currentUser);
        $news->delete();
        $this->newsRepository->flush();

        $this->flashMessage('News successfully deleted');
        $this->redirect('Homepage:dashboard');
    }
}
