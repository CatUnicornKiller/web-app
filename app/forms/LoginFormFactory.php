<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\IUserStorage;
use Nette\Security\User;

/**
 * Class containing factory methods for forms mainly concerning user login.
 * Alongside factories there can also be success callbacks.
 */
class LoginFormFactory
{
    use Nette\SmartObject;

    /** @var User */
    private $user;

    /**
     * DI Constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     * Create user login form.
     * @return Form
     */
    public function create()
    {
        $form = new MyForm;
        $form->addText('username', 'Username')
                ->setRequired('Please enter your username.')
                ->setHtmlAttribute('autofocus');

        $form->addPassword('password', 'Password')
            ->setRequired('Please enter your password.');

        $form->addCheckbox('remember', 'Keep me signed in');

        $form->addSubmit('send', 'Sign in');

        $form->onSuccess[] = array($this, 'formSucceeded');
        return $form;
    }

    /**
     * Success callback for the user login form.
     * @param MyForm $form
     * @param object $values
     */
    public function formSucceeded(MyForm $form, $values)
    {
        if ($values->remember) {
            $this->user->setExpiration('30 days', IUserStorage::CLEAR_IDENTITY);
        } else {
            $this->user->setExpiration('60 minutes', IUserStorage::CLEAR_IDENTITY);
        }

        try {
            $this->user->login($values->username, $values->password);
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
}
