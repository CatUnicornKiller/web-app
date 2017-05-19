<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model\Entity\User;
use App\Model\Entity\News;
use App\Model\Repository\Users;
use App\Model\Repository\NewsRepository;
use App\Users\UserManager;

/**
 * Class containing factory methods for forms mainly concerning homepage.
 * Alongside factories there can also be success callbacks.
 */
class HomepageFormsFactory extends Nette\Object
{
    /** @var User */
    private $user;
    /** @var Users */
    private $users;
    /** @var NewsRepository */
    private $newsRepository;

    /**
     * DI Constructor.
     * @param UserManager $userManager
     * @param Users $users
     * @param NewsRepository $newsRepository
     */
    public function __construct(
        UserManager $userManager,
        Users $users,
        NewsRepository $newsRepository
    ) {
        $this->user = $userManager->getCurrentUser();
        $this->users = $users;
        $this->newsRepository = $newsRepository;
    }

    /**
     * Create add news form.
     * @return \App\Forms\MyForm
     */
    public function createAddNewsForm()
    {
        $form = new MyForm;
        $form->addTextArea('message', 'Message')
                ->setRequired('Message is required')
                ->addRule(Form::MAX_LENGTH, 'Message is too long', 1000)
                ->setAttribute('length', 1000);
        $form->addSubmit('send', 'Add News');
        $form->onSuccess[] = array($this, 'addNewsFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the add news form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function addNewsFormSucceeded(MyForm $form, $values)
    {
        $news = new News($this->user, $values->message);
        $news->modified($this->user);
        $this->newsRepository->persist($news);
    }
}
