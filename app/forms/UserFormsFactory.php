<?php

namespace App\Forms;

use DateTime;
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
    /** @var User|null */
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
     * @return MyForm
     */
    public function createChangePasswdForm()
    {
        $form = new MyForm;
        $form->addPassword('oldPasswd', 'Old password')
                ->setRequired('Old password is required')
                ->setHtmlAttribute('autofocus');
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
     * @param MyForm $form
     * @param object $values
     */
    public function changePasswdFormSucceeded(MyForm $form, $values)
    {
        if (!$this->user->matchPasswords($values->oldPasswd)) {
            $form->addError('Old password does not match');
            return;
        }

        $this->user->hashPassword($values->passwd);
        $this->users->flush();
    }

    /**
     * Create edit current user form.
     * @return MyForm
     */
    public function createEditUserForm()
    {
        $officer = false;
        if ($this->user->isOfficer()) {
            $officer = $this->user->getOfficersProfile();
        }

        $form = new MyForm;
        $form->addText('id', 'ID')->setDisabled()
                ->setDefaultValue($this->user->getId());
        $form->addText('username', 'Username')->setDisabled()
                ->setDefaultValue($this->user->getUsername());
        $form->addUpload('profileImg', 'Profile Image');
        $form->addText('firstname', 'Firstname')
                ->setDefaultValue($this->user->getFirstname())
                ->setRequired('Firstname cannot be empty')
                ->addRule(Form::MAX_LENGTH, 'Firstname is too long', 255)
                ->setHtmlAttribute('length', 255);
        $form->addText('surname', 'Surname')
                ->setDefaultValue($this->user->getSurname())
                ->setRequired('Surname cannot be empty')
                ->addRule(Form::MAX_LENGTH, 'Surname is too long', 255)
                ->setHtmlAttribute('length', 255);
        $form->addText('email', 'Email')->setType('email')
                ->setDefaultValue($this->user->getEmail())
                ->setRequired('Email cannot be empty')
                ->addRule(Form::MAX_LENGTH, 'Email is too long', 255)
                ->setHtmlAttribute('length', 255)
                ->addRule(Form::EMAIL, 'Email is in bad format');
        if ($officer && $this->netteUser->isAllowed('MyIfmsaCredentials', 'edit')) {
            $form->addText('ifmsaUsername', 'IFMSA Username')
                    ->setDefaultValue($officer->getIfmsaUsername());
            $form->addText('ifmsaPassword', 'IFMSA Password')
                    ->setDefaultValue($officer->getIfmsaPassword());
                    //->setType('password');
        }
        $form->addText('faculty', 'Faculty')->setDisabled()
                ->setDefaultValue($this->user->getFaculty()->getFacultyName());
        $form->addText('country', 'Country')->setDisabled()
                ->setDefaultValue($this->user->getCountry()->getCountryName());
        $form->addText('role', 'Role')->setDisabled()
                ->setDefaultValue($this->rolesManager->roleToStr($this->user->getRole()));
        if ($officer) {
            $form->addText('address', 'Address')
                    ->setDefaultValue($officer->getAddress());
            $form->addText('city', 'City')
                    ->setDefaultValue($officer->getCity());
            $form->addText('postCode', 'Post Code')
                    ->setDefaultValue($officer->getPostCode());
            $form->addText('region', 'Region')
                    ->setDefaultValue($officer->getRegion());
            $form->addText('phone', 'Phone')
                    ->setDefaultValue($officer->getPhone());
        }
        $form->addSubmit('send', 'Edit User info');
        $form->onSuccess[] = array($this, 'editUserFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the edit current user form.
     * @param MyForm $form
     * @param object $values
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
            $profileImg = $this->user->getId() . '.' . $imgExt;
        }

        // save general things about user
        $user = $this->user;
        $user->setFirstname($values->firstname);
        $user->setSurname($values->surname);
        $user->setEmail($values->email);
        if (!empty($profileImg)) {
            $user->setProfileImg($profileImg);
        }

        // ... and now save officer specialities
        if ($this->user->isOfficer()) {
            $officer = $this->user->getOfficersProfile();
            $officer->setAddress($values->address);
            $officer->setCity($values->city);
            $officer->setPostCode($values->postCode);
            $officer->setRegion($values->region);
            $officer->setPhone($values->phone);

            if ($this->netteUser->isAllowed('MyIfmsaCredentials', 'edit')) {
                $officer->setIfmsaUsername($values->ifmsaUsername);
                $officer->setIfmsaPassword($values->ifmsaPassword);

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
     * @param object $values
     */
    private function updateInfoRequests($values)
    {
        $requestedDesc[] = "firstname";
        $requestedDesc[] = "surname";
        $requestedDesc[] = "email";
        if ($this->myAuthorizator->isAllowedUsers(
            'changeIfmsa',
            $this->user->getId(),
            $this->user->getFaculty()->getId(),
            $this->user->getRole()
        )) {
            $requestedDesc[] = "ifmsa_username";
            $requestedDesc[] = "ifmsa_password";
        }

        foreach ($this->user->getInfoRequests() as $request) {
            if (in_array($request->requestDesc, $requestedDesc) ||
                    ($request->requestDesc == "additional_information"
                    && $this->userHelpers->isAdditionalInfoFilled($values))) {
                $request->delete();
                $request->deletedByUser = $this->user;
                $request->deletedTime = new DateTime;
                $request->completed = true;
            }
        }

        $this->infoRequests->flush();
    }
}
