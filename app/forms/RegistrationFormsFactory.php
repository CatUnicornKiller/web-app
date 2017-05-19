<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App;
use App\Model\Entity\User;
use App\Model\Repository\Users;
use App\Model\Repository\Faculties;
use App\Model\Repository\Countries;

/**
 * Class containing factory methods for forms mainly concerning registration of
 * new users. Alongside factories there can also be success callbacks.
 */
class RegistrationFormsFactory extends Nette\Object
{
    /** @var Users */
    protected $users;
    /** @var Faculties */
    protected $faculties;
    /** @var Countries */
    protected $countries;
    /** @var App\Helpers\HumanDetectorFormHelper */
    protected $humanDetector;

    /**
     * DI Constructor.
     * @param Users $users
     * @param Faculties $faculties
     * @param Countries $countries
     * @param App\Helpers\HumanDetectorFormHelper $humanDetector
     */
    public function __construct(
        Users $users,
        Faculties $faculties,
        Countries $countries,
        App\Helpers\HumanDetectorFormHelper $humanDetector
    ) {

        $this->users = $users;
        $this->faculties = $faculties;
        $this->countries = $countries;
        $this->humanDetector = $humanDetector;
    }

    /**
     * Get faculties list for the selection box.
     * @return array
     */
    private function getFacultiesSelectList()
    {
        $faculties = $this->faculties->findAll();
        $facArr = array();
        foreach ($faculties as $fac) {
            $facArr[$fac->id] = $fac->facultyName;
        }
        return $facArr;
    }

    /**
     * Creates basic registration form with all general fields.
     * @return \App\Forms\MyForm
     */
    private function constructBasicForm()
    {
        $form = new MyForm;

        $form->addText('username', '*Username')
                ->setRequired('Please enter your username.')
                ->addRule(Form::MAX_LENGTH, 'Username is too long', 255)
                ->setAttribute('length', 255)
                ->setAttribute('autofocus');

        $form->addPassword('password', '*Password')
                ->setRequired('Please enter your password.');
        $form->addPassword('password_retype', '*Password again')
                ->setRequired('Please enter your password.')
                ->addRule(Form::EQUAL, 'Passwords are not the same', $form['password']);

        $form->addText('firstname', '*Firstname')
                ->setRequired('Please enter your firstname.')
                ->addRule(Form::MAX_LENGTH, 'Firstname is too long', 255)
                ->setAttribute('length', 255);
        $form->addText('surname', '*Surname')
                ->setRequired('Please enter your surname.')
                ->addRule(Form::MAX_LENGTH, 'Surname is too long', 255)
                ->setAttribute('length', 255);

        $form->addText('email', '*Email')
                ->setType('email')
                ->setRequired('Please enter your email.')
                ->addRule(Form::MAX_LENGTH, 'Email is too long', 255)
                ->setAttribute('length', 255)
                ->addRule(Form::EMAIL, 'Email is in bad format');

        $countries = $this->countries->findAll();
        $countArr = array();
        foreach ($countries as $country) {
            $countArr[$country->id] = $country->countryName;
        }
        $form->addSelect('country', '*Country', $countArr)
                ->setRequired('Please select country.')
                ->setValue(60);

        $this->humanDetector->addToForm($form);
        $form->addSubmit('send', 'Register now');

        return $form;
    }

    /**
     * Check registration form for the errors.
     * @return bool true if error was found
     */
    private function checkRegistrationForm(MyForm $form, $values)
    {
        $error = false;
        if (!$this->humanDetector->checkForm($form, $values)) {
            $error = true;
        }

        return $error;
    }

    /**
     * Create registration form for officers.
     * @return MyForm
     */
    public function createOfficersForm()
    {
        $form = $this->constructBasicForm();

        $form->addText('ifmsaUsername', 'IFMSA Username');
        $form->addPassword('ifmsaPasswd', 'IFMSA Password');

        $form->addSelect('faculty', '*Faculty', $this->getFacultiesSelectList())
            ->setPrompt('Choose faculty')
            ->setRequired('Please select faculty.');

        $form->onSuccess[] = array($this, 'officersFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the officer registration form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function officersFormSucceeded(MyForm $form, $values)
    {
        // if there was error/errors in previous conditions than return
        if ($this->checkRegistrationForm($form, $values)) {
            return;
        }

        if ($this->users->findByUsername($values->username)) {
            $form->addError('Duplicate username, please choose another one.');
            return;
        }

        $user = User::createOfficer(
            $values->username,
            $values->firstname,
            $values->surname,
            $values->email,
            $values->password,
            'nobody',
            $this->faculties->get($values->faculty),
            $this->countries->get($values->country),
            $values->ifmsaUsername,
            $values->ifmsaPasswd
        );
        $user->modified($user);
        $this->users->persist($user);
    }

    /**
     * Create registration form for incomings.
     * @return MyForm
     */
    public function createIncomingsForm()
    {
        $form = $this->constructBasicForm();

        $form->addSelect('faculty', '*Faculty', $this->getFacultiesSelectList())
            ->setPrompt('Choose the faculty (or the country), where you attend your exchange')
            ->setRequired('Please select faculty.');

        $form->onSuccess[] = array($this, 'incomingsFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the incomings registration form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function incomingsFormSucceeded(MyForm $form, $values)
    {
        // if there was error/errors in previous conditions than return
        if ($this->checkRegistrationForm($form, $values)) {
            return;
        }

        if ($this->users->findByUsername($values->username)) {
            $form->addError('Duplicate username, please choose another one.');
            return;
        }

        $user = User::createIncoming(
            $values->username,
            $values->firstname,
            $values->surname,
            $values->email,
            $values->password,
            'nobody',
            $this->faculties->get($values->faculty),
            $this->countries->get($values->country)
        );
        $user->modified($user);
        $this->users->persist($user);
    }
}
