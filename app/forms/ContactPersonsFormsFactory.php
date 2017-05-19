<?php

namespace App\Forms;

use Nette;
use App;
use App\Model\Entity\User;
use App\Model\Entity\IfmsaPerson;
use App\Model\Entity\CpAssignedAf;
use App\Users\UserManager;
use App\Model\Repository\Users;
use App\Model\Repository\CpAssignedAfs;
use App\Model\Repository\IfmsaPersons;

/**
 * Class containing factory methods for forms mainly concerning contact persons
 * management. Alongside factories there can also be success callbacks.
 */
class ContactPersonsFormsFactory extends Nette\Object
{
    /** @var User */
    private $user;
    /** @var Users */
    private $users;
    /** @var CpAssignedAfs */
    private $assignedAfs;
    /** @var App\Helpers\Pdf\IfmsaRemotePdfFactory */
    private $ifmsaPdfModel;
    /** @var App\Helpers\IfmsaConnectionHelper */
    private $ifmsaConnectionHelper;
    /** @var IfmsaPersons */
    private $ifmsaPersons;
    /** @var \App\Helpers\StringHelper */
    private $stringHelpers;
    /** @var Nette\Http\Request */
    private $httpRequest;
    /** @var App\Users\MyAuthorizator */
    private $myAuthorizator;

    /**
     * DI Constructor.
     * @param UserManager $userManager
     * @param Users $users
     * @param App\Helpers\StringHelper $stringHelpers
     * @param CpAssignedAfs $assignedAfs
     * @param App\Helpers\Pdf\IfmsaRemotePdfFactory $ifmsaPdfModel
     * @param App\Helpers\IfmsaConnectionHelper $ifmsaConnectionHelper
     * @param IfmsaPersons $ifmsaPersons
     * @param Nette\Http\Request $httpRequest
     * @param App\Users\MyAuthorizator $myAuthorizator
     */
    public function __construct(
        UserManager $userManager,
        Users $users,
        App\Helpers\StringHelper $stringHelpers,
        CpAssignedAfs $assignedAfs,
        App\Helpers\Pdf\IfmsaRemotePdfFactory $ifmsaPdfModel,
        App\Helpers\IfmsaConnectionHelper $ifmsaConnectionHelper,
        IfmsaPersons $ifmsaPersons,
        Nette\Http\Request $httpRequest,
        App\Users\MyAuthorizator $myAuthorizator
    ) {

        $this->user = $userManager->getCurrentUser();
        $this->users = $users;
        $this->stringHelpers = $stringHelpers;
        $this->assignedAfs = $assignedAfs;
        $this->ifmsaPdfModel = $ifmsaPdfModel;
        $this->ifmsaConnectionHelper = $ifmsaConnectionHelper;
        $this->ifmsaPersons = $ifmsaPersons;
        $this->httpRequest = $httpRequest;
        $this->myAuthorizator = $myAuthorizator;
    }

    public function createFindContactPersonForm($afNumber)
    {
        $form = new MyForm();

        $officers = $this->users->getFacultyOfficers($this->user->faculty);
        $cps = array();
        foreach ($officers as $officer) {
            $cps[$officer->id] = $officer->firstname . ' ' . $officer->surname . ' (' . $officer->email . ')';
        }

        $form->addRadioList('userId', 'Choose Officer', $cps)
                ->setRequired('You have to choose officer');
        $form->addHidden('afNumber', $afNumber);
        $form->addSubmit('send', 'Choose Officer');
        return $form;
    }

    private function addDepartmentAccomodation(MyForm $form)
    {
        $form->addHidden('afNumber');
        $form->addHidden('userId');

        $depStr = 'Department';
        if (!$this->myAuthorizator->isScope()) {
            $depStr = 'Project';
        }
        $deps = array(
            'dep1' => $depStr . ' 1',
            'dep2' => $depStr . ' 2',
            'dep3' => $depStr . ' 3'
            );
        if ($this->myAuthorizator->isScope()) {
            $deps['dep4'] = $depStr . ' 4';
        }
        $deps['other'] = '';

        $form->addRadioList('department', $depStr, $deps)
                ->setRequired('You have to choose ' . $depStr);
        $form->addText('otherDepartment');
        $form->addTextArea('accommodation', 'Accommodation');

        $pi = $form->addContainer('personInfo');
        foreach ($this->ifmsaConnectionHelper->initializePersonInfo() as $val) {
            $pi->addHidden($this->stringHelpers->alphaNumText($val));
        }
    }

    private function getDefaultDepartment($department, array $personInfo)
    {
        if ($personInfo['department1'] == $department) {
            return 'dep1';
        } elseif ($personInfo['department2'] == $department) {
            return 'dep2';
        } elseif ($personInfo['department3'] == $department) {
            return 'dep3';
        } elseif ($this->myAuthorizator->isScope() && $personInfo['department4'] == $department) {
            return 'dep4';
        }

        return 'other';
    }

    public function fillAfForm(MyForm $form, $afNumber, $userId, array $personInfo)
    {
        $person = $this->ifmsaPersons->findByAfNumber($afNumber);
        $form->setDefaults(array('afNumber' => $afNumber, 'userId' => $userId));

        $deps = array(
            'dep1' => $this->stringHelpers->getDepartmentDescription($personInfo['department1']),
            'dep2' => $this->stringHelpers->getDepartmentDescription($personInfo['department2']),
            'dep3' => $this->stringHelpers->getDepartmentDescription($personInfo['department3'])
        );
        if ($this->myAuthorizator->isScope()) {
            $deps['dep4'] = $this->stringHelpers->getDepartmentDescription($personInfo['department4']);
        }
        $deps['other'] = '';
        $form['department']->setItems($deps);

        // try to match saved department (in database) against given ones
        $defaultDep = $this->getDefaultDepartment($person->department, $personInfo);
        $form['department']->setDefaultValue($defaultDep);
        if ($defaultDep == 'other') {
            $form['otherDepartment']->setDefaultValue($person->department);
        }

        $form['accommodation']->setDefaultValue($person->accommodation);

        $defs = array();
        foreach ($personInfo as $key => $value) {
            if (is_scalar($value)) {
                $defs[$this->stringHelpers->alphaNumText($key)] = $value;
            }
        }
        $form['personInfo']->setDefaults($defs);
    }

    public function createAfAssignForm()
    {
        $form = new MyForm();
        $this->addDepartmentAccomodation($form);
        $form->addSubmit('send', 'Assign');
        $form->onSuccess[] = array($this, 'afAssignFormSucceeded');
        return $form;
    }

    public function createAfRefreshForm()
    {
        $form = new MyForm();
        $this->addDepartmentAccomodation($form);
        $form->addSubmit('send', 'Refresh');
        $form->onSuccess[] = array($this, 'afRefreshFormSucceeded');
        return $form;
    }

    private function determineDepartment($dep, $personInfo, $other = '')
    {
        if ($dep == 'other') {
            $personInfo['departmentChosen'] = $other;
        } elseif ($dep == 'dep4') {
            $personInfo['departmentChosen'] = $personInfo['department4'];
        } elseif ($dep == 'dep3') {
            $personInfo['departmentChosen'] = $personInfo['department3'];
        } elseif ($dep == 'dep2') {
            $personInfo['departmentChosen'] = $personInfo['department2'];
        } else {
            $personInfo['departmentChosen'] = $personInfo['department1'];
        }
    }

    private function getCardAndUpdate($afNumber, $accm, & $personInfo, array & $cardOfDocuments)
    {
        try {
            $this->ifmsaConnectionHelper->fetchPersonCard(
                $afNumber,
                $personInfo,
                $cardOfDocuments
            );
        } catch (\Exception $e) {
        }

        $personInfo['accommodation'] = $accm;

        // check values in database and update them
        $person = $this->ifmsaPersons->findByAfNumber($afNumber);
        if ($person) {
            $person->firstname = $personInfo['name'];
            $person->surname = $personInfo['surname'];
            $person->email = $personInfo['email'];
            $person->photo = $personInfo['jpgPath'];
            $person->afArrival = new \DateTime($personInfo['arrivalDate']);
            $this->ifmsaPersons->flush();
        } else {
            $person = new IfmsaPerson(
                $afNumber,
                $personInfo['confirmationNumber'],
                $personInfo['name'],
                $personInfo['surname'],
                $personInfo['email'],
                $personInfo['jpgPath'],
                $personInfo['arrivalDate']
            );
            $this->ifmsaPersons->persist($person);
        }
    }

    public function afAssignFormSucceeded(MyForm $form, $values)
    {
        $assign = $this->assignedAfs->findOneByAfNumber($values->afNumber);
        if ($assign) {
            $form->addError('Already assigned!');
            return;
        }

        $afArrival = date_create_from_format("d/m/Y", $values->personInfo["arrivalDate"]);
        if (!$afArrival) {
            $afArrival = "0000-00-00";
        } else {
            $afArrival->format("Y-m-d");
        }

        $cardOfDocuments = array();
        $this->getCardAndUpdate(
            $values->afNumber,
            $values->accommodation,
            $values->personInfo,
            $cardOfDocuments
        );
        $this->determineDepartment($values->department, $values->personInfo, $values->otherDepartment);

        // update ifmsa person information in database
        $person = $this->ifmsaPersons->findByAfNumber($values->afNumber);
        $person->accommodation = $values->accommodation;
        $person->department = $values->personInfo['departmentChosen'];
        $this->ifmsaPersons->flush();

        // store assigned incoming
        $user = $this->users->findOrThrow($values->userId);
        $assigned = new CpAssignedAf(
            $user,
            $values->afNumber,
            $values->personInfo["name"] . " " . $values->personInfo["surname"],
            $afArrival
        );
        $assigned->modified($this->user);
        $this->assignedAfs->persist($assigned);

        $this->ifmsaPdfModel->generateContactPersonPdf(
            $values->personInfo,
            $cardOfDocuments,
            substr($_SERVER["SCRIPT_FILENAME"], 0, strlen($_SERVER["SCRIPT_FILENAME"]) - strlen("index.php"))
            . '/pdf/' . $values->afNumber . '.pdf'
        );
    }

    public function afRefreshFormSucceeded(MyForm $form, $values)
    {
        $assigned = $this->assignedAfs->findOneByAfNumber($values->afNumber);
        if (!$assigned) {
            $form->addError('Not assigned!');
            return;
        }

        $afArrival = date_create_from_format("d/m/Y", $values->personInfo["arrivalDate"]);
        if (!$afArrival) {
            $afArrival = new \DateTime("0000-00-00");
        }

        $cardOfDocuments = array();
        $this->getCardAndUpdate(
            $values->afNumber,
            $values->accommodation,
            $values->personInfo,
            $cardOfDocuments
        );
        $this->determineDepartment($values->department, $values->personInfo, $values->otherDepartment);

        // update ifmsa person information in database
        $person = $this->ifmsaPersons->findByAfNumber($values->afNumber);
        $person->accommodation = $values->accommodation;
        $person->department = $values->personInfo['departmentChosen'];
        $this->ifmsaPersons->flush();

        // update assigned incoming
        $assigned->afArrival = $afArrival;
        $assigned->modified($this->user);
        $this->assignedAfs->flush();

        $this->ifmsaPdfModel->generateContactPersonPdf(
            $values->personInfo,
            $cardOfDocuments,
            substr($_SERVER["SCRIPT_FILENAME"], 0, strlen($_SERVER["SCRIPT_FILENAME"]) - strlen("index.php"))
            . '/pdf/' . $values->afNumber . '.pdf'
        );
    }
}
