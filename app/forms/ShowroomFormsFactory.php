<?php

namespace App\Forms;

use Exception;
use Nette;
use Nette\Application\UI\Form;
use App;
use App\Model\Entity\Showroom;
use App\Model\Entity\User;
use App\Model\Repository\ShowroomRepository;
use App\Model\Repository\Faculties;
use App\Users\UserManager;
use function copy;

/**
 * Class containing factory methods for forms mainly concerning showroom.
 * Alongside factories there can also be success callbacks.
 */
class ShowroomFormsFactory
{
    use Nette\SmartObject;

    /** @var User|null */
    private $user;
    /** @var ShowroomRepository */
    private $showroomRepository;
    /** @var App\Users\RolesManager */
    private $rolesManager;
    /** @var Faculties */
    private $faculties;
    /** @var App\Helpers\ConfigParams */
    private $configParams;

    /**
     * DI Constructor.
     * @param UserManager $userManager
     * @param ShowroomRepository $showroomRepository
     * @param App\Users\RolesManager $rolesManager
     * @param Faculties $faculties
     * @param App\Helpers\ConfigParams $configParameters
     */
    public function __construct(
        UserManager $userManager,
        ShowroomRepository $showroomRepository,
        App\Users\RolesManager $rolesManager,
        Faculties $faculties,
        App\Helpers\ConfigParams $configParameters
    ) {

        $this->user = $userManager->getCurrentUser();
        $this->showroomRepository = $showroomRepository;
        $this->rolesManager = $rolesManager;
        $this->faculties = $faculties;
        $this->configParams = $configParameters;
    }

    /**
     * Get faculties list for the selection box.
     * @return array
     */
    private function getFacultiesSelect()
    {
        $res = array();
        foreach ($this->faculties->findAll() as $fac) {
            $res[$fac->getId()] = $fac->getFacultyName();
        }
        return $res;
    }

    /**
     * Get list of years for the selection box.
     * @return array
     */
    private function getYearSelect()
    {
        $res = array();
        for ($i = 2014; $i <= (date('Y') + 1); ++$i) {
            $res[$i] = $i;
        }
        return $res;
    }

    /**
     * Create basic showroom form representing officer.
     * @param User|Showroom|null $officer
     * @return MyForm
     */
    private function createOfficerForm($officer)
    {
        $form = new MyForm;

        $form->addText('firstname', 'Firstname')
                ->setRequired('Firstname is required')
                ->addRule(Form::MAX_LENGTH, 'Firstname is too long', 255)
                ->setHtmlAttribute('length', 255);
        $form->addText('surname', 'Surname')
                ->setRequired('Surname is required')
                ->addRule(Form::MAX_LENGTH, 'Surname is too long', 255)
                ->setHtmlAttribute('length', 255);
        $form->addSelect('role', 'Role', $this->rolesManager->getShowroomRoles())
                ->setRequired('Role is required');
        $form->addSelect('faculty', 'Faculty', $this->getFacultiesSelect())
                ->setRequired('Faculty is required');
        $form->addSelect('startYear', 'Start Year', $this->getYearSelect())
                ->setRequired('Start Year is required')->setDefaultValue(date('Y'));
        $form->addSelect('endYear', 'End Year', $this->getYearSelect())
                ->setRequired('End Year is required')->setDefaultValue(date('Y') + 1);
        $form->addCheckbox('useProfileImg', 'Use current profile image?')
                ->setDefaultValue(true);
        $form->addUpload('uploadImg', 'Upload new image');
        $form->addHidden('profileImg');
        $form->addTextArea('information', 'Information')
                ->setRequired('Information is required')
                ->addRule(Form::MAX_LENGTH, 'Information is too long', 1000)
                ->setHtmlAttribute('length', 1000);

        if ($officer) {
            try {
                $form->setDefaults(array(
                    'firstname' => $officer->getFirstname(),
                    'surname' => $officer->getSurname(),
                    'role' => $officer->getRole(),
                    'faculty' => $officer->getFaculty()->getId(),
                    'profileImg' => $officer->getProfileImg()
                        ));
            } catch (Exception $e) {
            }
        }

        return $form;
    }

    /**
     * Create add officer to the showroom form.
     * @param User $officer
     * @return MyForm
     */
    public function createAddOfficerForm($officer = null)
    {
        $form = $this->createOfficerForm($officer);

        $form->addSubmit('send', 'Add Officer');
        $form->onSuccess[] = array($this, 'addOfficerFormSucceeded');
        return $form;
    }

    /**
     * Check add officer form for the errors.
     * @param MyForm $form
     * @param object $values
     * @return boolean true if form is valid
     */
    private function checkAddOfficer(MyForm $form, $values)
    {
        if ($values->uploadImg->isOk() && !$values->uploadImg->isImage()) {
            $form->addError('Uploaded file is not an image');
            return false;
        } elseif ($values->uploadImg->isOk() && $values->uploadImg->getSize() > (5 * 1024 * 1024)) {
            $form->addError('Uploaded image has to be up to 5MB');
            return false;
        }
        return true;
    }

    /**
     * Success callback for the add officer to the showroom form.
     * @param MyForm $form
     * @param object $values
     */
    public function addOfficerFormSucceeded(MyForm $form, $values)
    {
        if (!$this->checkAddOfficer($form, $values)) {
            return;
        }

        $faculty = $this->faculties->findOrThrow($values->faculty);
        $showroom = new Showroom(
            $values->firstname,
            $values->surname,
            $values->role,
            $faculty,
            $values->startYear,
            $values->endYear,
            "",
            $values->information
        );
        $this->showroomRepository->persist($showroom);

        if ($values->uploadImg->isOk()) {
            $image = $showroom->getId() . '.' . pathinfo($values->uploadImg->sanitizedName, PATHINFO_EXTENSION);
            $values->uploadImg->move(getcwd() . $this->configParams->showroomImgDir . $image);

            $showroom->setProfileImg($image);
            $this->showroomRepository->flush();
        } elseif ($values->useProfileImg && !empty($values->profileImg)) {
            $image = $showroom->getId() . '.' . pathinfo($values->profileImg, PATHINFO_EXTENSION);
            copy(
                getcwd() . $this->configParams->profileImgDir . $values->profileImg,
                getcwd() . $this->configParams->showroomImgDir . $image
            );

            $showroom->setProfileImg($image);
            $this->showroomRepository->flush();
        }
    }

    /**
     * Create showroom entry editation form.
     * @param Showroom $officer
     * @return MyForm
     */
    public function createEditOfficerForm($officer)
    {
        $form = $this->createOfficerForm($officer);
        $form->addHidden('id', $officer->getId());

        try {
            $form->setDefaults(array(
                'startYear' => $officer->getStartYear(),
                'endYear' => $officer->getEndYear(),
                'information' => $officer->getInformation(),
                    ));
        } catch (Exception $e) {
        }

        $form->addSubmit('send', 'Edit Showroom Officer');
        $form->onSuccess[] = array($this, 'editOfficerFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the showroom entry editation form.
     * @param MyForm $form
     * @param object $values
     */
    public function editOfficerFormSucceeded(MyForm $form, $values)
    {
        if (!$this->checkAddOfficer($form, $values)) {
            return;
        }

        $faculty = $this->faculties->findOrThrow($values->faculty);
        $showroom = $this->showroomRepository->findOrThrow($values->id);

        if ($values->uploadImg->isOk()) {
            $image = $values->id . '.' . pathinfo($values->uploadImg->sanitizedName, PATHINFO_EXTENSION);
            $values->uploadImg->move(getcwd() . $this->configParams->showroomImgDir . $image);

            $showroom->setProfileImg($image);
        } elseif (!$values->useProfileImg) {
            $showroom->setProfileImg("");
        }

        $showroom->setFirstname($values->firstname);
        $showroom->setSurname($values->surname);
        $showroom->setRole($values->role);
        $showroom->setFaculty($faculty); // @phpstan-ignore-line
        $showroom->setStartYear($values->startYear);
        $showroom->setEndYear($values->endYear);
        $showroom->setInformation($values->information);
        $this->showroomRepository->flush();
    }
}
