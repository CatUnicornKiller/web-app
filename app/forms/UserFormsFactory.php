<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App;
use App\Model\Repository\Users;
use App\Model\Repository\UserInfoRequests;
use App\Model\Entity\User;
use App\Users\UserManager;

/**
 * Class containing factory methods mainly concerning logged user forms.
 * Alongside factories there can also be success callbacks.
 */
class UserFormsFactory
{
    use Nette\SmartObject;

    /** @var Nette\Security\User */
    private $netteUser;
    /** @var User */
    private $user;
    /** @var Users */
    private $users;
    /** @var UserInfoRequests */
    private $infoRequests;
    /** @var App\Users\RolesManager */
    private $rolesManager;
    /** @var App\Users\MyAuthorizator */
    private $myAuthorizator;
    /** @var App\Helpers\GuzzleFactory */
    private $guzzleFactory;
    /** @var App\Helpers\UserHelper */
    private $userHelpers;
    /** @var App\Helpers\ConfigParams */
    private $configParams;


    /**
     * DI Constructor.
     * @param Nette\Security\User $user
     * @param UserManager $userManager
     * @param Users $usersModel
     * @param App\Users\RolesManager $rolesManager
     * @param App\Helpers\GuzzleFactory $guzzleFactory
     * @param App\Users\MyAuthorizator $myAuthorizator
     * @param UserInfoRequests $infoRequests
     * @param App\Helpers\UserHelper $userHelpers
     * @param App\Helpers\ConfigParams $configParams
     */
    public function __construct(
        Nette\Security\User $user,
        UserManager $userManager,
        Users $usersModel,
        App\Users\RolesManager $rolesManager,
        App\Helpers\GuzzleFactory $guzzleFactory,
        App\Users\MyAuthorizator $myAuthorizator,
        UserInfoRequests $infoRequests,
        App\Helpers\UserHelper $userHelpers,
        App\Helpers\ConfigParams $configParams
    ) {
        $this->netteUser = $user;
        $this->user = $userManager->getCurrentUser();
        $this->users = $usersModel;
        $this->rolesManager = $rolesManager;
        $this->guzzleFactory = $guzzleFactory;
        $this->myAuthorizator = $myAuthorizator;
        $this->infoRequests = $infoRequests;
        $this->userHelpers = $userHelpers;
        $this->configParams = $configParams;
    }

    /**
     * Create change current user password form.
     * @return \App\Forms\MyForm
     */
    public function createChangePasswdForm()
    {
        $form = new MyForm;
        $form->addPassword('oldPasswd', 'Old password')
                ->setRequired('Old password is required')
                ->setAttribute('autofocus');
        $form->addPassword('passwd', 'New password')
                ->setRequired('New password is required');
        $form->addPassword('retypePasswd', 'Retype new password')
                ->setRequired('Retype new password is required')
                ->addRule(Form::EQUAL, 'New passwords do not match', $form['passwd']);
        $form->addSubmit('send', 'Change Password');
        $form->onSuccess[] = array($this, 'changePasswdFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the change password form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function changePasswdFormSucceeded(MyForm $form, $values)
    {
        if (!Nette\Security\Passwords::verify($values->oldPasswd, $this->user->password)) {
            $form->addError('Old password does not match');
            return;
        }

        $this->user->password = $values->passwd;
        $this->users->flush();
    }

    /**
     * Create edit current user form.
     * @return \App\Forms\MyForm
     */
    public function createEditUserForm()
    {
        $officer = false;
        if ($this->user->isOfficer()) {
            $officer = $this->user->officersProfile;
        }

        $form = new MyForm;
        $form->addText('id', 'ID')->setDisabled()
                ->setDefaultValue($this->user->id);
        $form->addText('username', 'Username')->setDisabled()
                ->setDefaultValue($this->user->username);
        $form->addUpload('profileImg', 'Profile Image');
        $form->addText('firstname', 'Firstname')
                ->setDefaultValue($this->user->firstname)
                ->setRequired('Firstname cannot be empty')
                ->addRule(Form::MAX_LENGTH, 'Firstname is too long', 255)
                ->setAttribute('length', 255);
        $form->addText('surname', 'Surname')
                ->setDefaultValue($this->user->surname)
                ->setRequired('Surname cannot be empty')
                ->addRule(Form::MAX_LENGTH, 'Surname is too long', 255)
                ->setAttribute('length', 255);
        $form->addText('email', 'Email')->setType('email')
                ->setDefaultValue($this->user->email)
                ->setRequired('Email cannot be empty')
                ->addRule(Form::MAX_LENGTH, 'Email is too long', 255)
                ->setAttribute('length', 255)
                ->addRule(Form::EMAIL, 'Email is in bad format');
        if ($officer && $this->netteUser->isAllowed('MyIfmsaCredentials', 'edit')) {
            $form->addText('ifmsaUsername', 'IFMSA Username')
                    ->setDefaultValue($officer->ifmsaUsername);
            $form->addText('ifmsaPassword', 'IFMSA Password')
                    ->setDefaultValue($officer->ifmsaPassword);
                    //->setType('password');
        }
        $form->addText('faculty', 'Faculty')->setDisabled()
                ->setDefaultValue($this->user->faculty->facultyName);
        $form->addText('country', 'Country')->setDisabled()
                ->setDefaultValue($this->user->country->countryName);
        $form->addText('role', 'Role')->setDisabled()
                ->setDefaultValue($this->rolesManager->roleToStr($this->user->role));
        if ($officer) {
            $form->addText('address', 'Address')
                    ->setDefaultValue($officer->address);
            $form->addText('city', 'City')
                    ->setDefaultValue($officer->city);
            $form->addText('postCode', 'Post Code')
                    ->setDefaultValue($officer->postCode);
            $form->addText('region', 'Region')
                    ->setDefaultValue($officer->region);
            $form->addText('phone', 'Phone')
                    ->setDefaultValue($officer->phone);
        }
        $form->addSubmit('send', 'Edit User info');
        $form->onSuccess[] = array($this, 'editUserFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the edit current user form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function editUserFormSucceeded(MyForm $form, $values)
    {
        $error = false;
        if ($values->profileImg->isOk() && !$values->profileImg->isImage()) {
            $form->addError('Uploaded file is not an image');
            $error = true;
        }
        if ($values->profileImg->isOk() && $values->profileImg->getSize() > (5 * 1024 * 1024)) {
            $form->addError('Uploaded image has to be up to 5MB');
            $error = true;
        }
        if ($error) {
            return;
        }

        // handle information requests
        $this->updateInfoRequests($values);

        // determine profile img
        $profileImg = "";
        if ($values->profileImg->isOk()) {
            $imgExt = pathinfo($values->profileImg->sanitizedName, PATHINFO_EXTENSION);
            $profileImg = $this->user->id . '.' . $imgExt;
        }

        // save general things about user
        $user = $this->user;
        $user->firstname = $values->firstname;
        $user->surname = $values->surname;
        $user->email = $values->email;
        if (!empty($profileImg)) {
            $user->profileImg = $profileImg;
        }

        // ... and now save officer specialities
        if ($this->user->isOfficer()) {
            $officer = $this->user->officersProfile;
            $officer->address = $values->address;
            $officer->city = $values->city;
            $officer->postCode = $values->postCode;
            $officer->region = $values->region;
            $officer->phone = $values->phone;

            if ($this->netteUser->isAllowed('MyIfmsaCredentials', 'edit')) {
                $officer->ifmsaUsername = $values->ifmsaUsername;
                $officer->ifmsaPassword = $values->ifmsaPassword;

                // in case of change of ifmsa credentials
                $this->guzzleFactory->destroySessionCookie();
            }
        }

        // and flush all changes
        $user->modified($user);
        $this->users->flush();

        // move uploadedfile
        if ($values->profileImg->isOk()) {
            $values->profileImg->move(getcwd() . $this->configParams->profileImgDir .
                    '/' . $profileImg);
        }
    }

    /**
     * If the current user fullfiled information requests, mark them as
     * completed in the database.
     * @param array $values
     */
    private function updateInfoRequests($values)
    {
        $requestedDesc[] = "firstname";
        $requestedDesc[] = "surname";
        $requestedDesc[] = "email";
        if ($this->myAuthorizator->isAllowedUsers(
            'changeIfmsa',
            $this->user->id,
            $this->user->faculty->id,
            $this->user->role
        )) {
            $requestedDesc[] = "ifmsa_username";
            $requestedDesc[] = "ifmsa_password";
        }

        foreach ($this->user->infoRequests as $request) {
            if (in_array($request->requestDesc, $requestedDesc) ||
                    ($request->requestDesc == "additional_information"
                    && $this->userHelpers->isAdditionalInfoFilled($values))) {
                $request->delete();
                $request->deletedByUser = $this->user;
                $request->deletedTime = new \DateTime;
                $request->completed = true;
            }
        }

        $this->infoRequests->flush();
    }
}
