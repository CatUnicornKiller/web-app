<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model\Entity\ForgottenPassword;
use App\Model\Entity\RenewedPassword;
use App\Model\Repository\Users;
use App\Model\Repository\ForgottenPasswords;
use App\Model\Repository\RenewedPasswords;

/**
 * Class containing factory methods for forms mainly concerning forgotten
 * password feature. Alongside factories there can also be success callbacks.
 */
class ForgottenPasswordFormsFactory extends Nette\Object
{
    /** @var Users */
    protected $users;
    /** @var ForgottenPasswords */
    protected $forgottenPasswords;
    /** @var RenewedPasswords */
    protected $renewedPasswords;
    /** @var Nette\Http\IRequest */
    protected $httpRequest;

    /**
     * DI Constructor.
     * @param Users $users
     * @param ForgottenPasswords $forgottenPasswords
     * @param RenewedPasswords $renewedPasswords
     * @param Nette\Http\IRequest $httpRequest
     */
    public function __construct(
        Users $users,
        ForgottenPasswords $forgottenPasswords,
        RenewedPasswords $renewedPasswords,
        Nette\Http\IRequest $httpRequest
    ) {

        $this->users = $users;
        $this->forgottenPasswords = $forgottenPasswords;
        $this->renewedPasswords = $renewedPasswords;
        $this->httpRequest = $httpRequest;
    }

    /**
     * Generate forgotten password token.
     * @param int $length
     * @return string
     */
    private function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Create forgotten password request form.
     * @return \App\Forms\MyForm
     */
    public function createForgottenPasswordForm()
    {
        $form = new MyForm;
        $form->addText('username', '*Username')
                ->setRequired('Please enter your username.')
                ->setAttribute('autofocus')
                ->addRule(Form::MAX_LENGTH, 'Username is too long', 255)
                ->setAttribute('length', 255);
        $form->addText('email', '*Email')
                ->setType('email')
                ->setRequired('Please enter your email.')
                ->addRule(Form::MAX_LENGTH, 'Email is too long', 255)
                ->setAttribute('length', 255)
                ->addRule(Form::EMAIL, 'Email is in bad format');

        $form->addSubmit('send', 'Send Email');

        $form->onSuccess[] = array($this, 'forgottenPasswordFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the forgotten password form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function forgottenPasswordFormSucceeded(MyForm $form, $values)
    {
        // action itself
        $user = $this->users->findByUsernameAndEmail($values->username, $values->email);
        if (!$user) {
            $form->addError('User with this username and email does not exist!');
            return;
        }

        // and creation and sending of email right here
        $ftoken = $this->generateToken(100);
        $forgottenPassword = new ForgottenPassword($user, $this->httpRequest->getRemoteAddress(), $ftoken);
        $this->forgottenPasswords->persist($forgottenPassword);

        // now send mail
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $mail_content = '
            Hello from CUK system,<br>
            your password can be renewed by clicking on this <a href="' . $this->httpRequest->getUrl()->baseUrl . 'homepage/renew/?ftoken=' . $ftoken . '">link</a>.<br>
            <span style="color:red;">Note that this link is valid for only 10 minutes.</span>
        ';
        if (!mail($values->email, "CatUnicornKiller System - Password recovery", $mail_content, $headers)) {
            $form->addError('Email was not send, try it again later, please.');
        }
    }

    /**
     * Create renew password form.
     * @param string $ftoken
     * @return \App\Forms\MyForm
     */
    public function createRenewPasswordForm($ftoken)
    {
        $form = new MyForm;
        $form->addText('username', '*Username')
                ->setRequired('Please enter your username.')
                ->setAttribute('autofocus')
                ->addRule(Form::MAX_LENGTH, 'Username is too long', 255)
                ->setAttribute('length', 255);
        $form->addText('email', '*Email')
                ->setType('email')
                ->setRequired('Please enter your email.')
                ->addRule(Form::MAX_LENGTH, 'Email is too long', 255)
                ->setAttribute('length', 255)
                ->addRule(Form::EMAIL, 'Email is in bad format');

        $form->addPassword('password', '*Password')
                ->setRequired('Please enter your new password.');
        $form->addPassword('retypePassword', '*Retype Password')
                ->setRequired('Please enter your new password.')
                ->addRule(Form::EQUAL, 'Passwords are not the same', $form['password']);

        $form->addSubmit('send', 'Renew password');
        $form->addHidden('ftoken', $ftoken);

        $form->onSuccess[] = array($this, 'renewPasswordFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the renew password form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function renewPasswordFormSucceeded(MyForm $form, $values)
    {
        $forgottenPassword = $this->forgottenPasswords->findOneActiveByToken($values->ftoken);
        if (!$forgottenPassword) {
            $form->addError('Expired link, try it again, please.');
            return;
        }

        $user = $forgottenPassword->user;

        $error = false;
        if ($user->username != $values->username) {
            $form->addError('Username does not match!');
            $error = true;
        } elseif ($user->email != $values->email) {
            $form->addError('Email does not match!');
            $error = true;
        }

        if ($error) {
            return;
        }

        $user->password = $values->password;
        $user->modified($user);
        $this->users->flush();

        $renewed = new RenewedPassword($user, $this->httpRequest->getRemoteAddress());
        $this->renewedPasswords->persist($renewed);
    }
}
