<?php

namespace App\Presenters;

use App;
use Nette;
use App\Model\Entity\IfmsaPerson;
use App\Model\Repository\CpAssignedAfs;
use App\Model\Repository\IfmsaPersons;
use App\Exceptions\IfmsaConnectionException;
use App\Helpers\Date\DateHelper;
use Traversable;

/**
 * Ifmsa presenter.
 */
class IfmsaPresenter extends BasePresenter
{
    /**
     * @var App\Helpers\IfmsaConnectionHelper
     * @inject
     */
    public $ifmsaConnectionHelper;
    /**
     * @var App\Forms\IfmsaFormsFactory
     * @inject
     */
    public $ifmsaFormsFactory;
    /**
     * @var App\Helpers\Pdf\IfmsaRemotePdfFactory
     * @inject
     */
    public $ifmsaRemotePdfFactory;
    /**
     * @var App\Helpers\ResponseHelper
     * @inject
     */
    public $responseHelpers;
    /**
     * @var App\Helpers\Table\IfmsaRemoteTableFactory
     * @inject
     */
    public $ifmsaRemoteTableFactory;
    /**
     * @var CpAssignedAfs
     * @inject
     */
    public $cpAssignedAfs;
    /**
     * @var IfmsaPersons
     * @inject
     */
    public $ifmsaPersons;
    /**
     * @var DateHelper
     * @inject
     */
    public $dateHelper;

    protected function startup()
    {
        parent::startup();
        $this->redirectIfUserHasEmptyIfmsaCredentials();
    }

    private function createComponentYearMonthForm($year, $month)
    {
        return $this->ifmsaFormsFactory->createYearMonthForm($year, $month);
    }

    private function createComponentPdfSelectionForm(array $personInfo, array $cardOfDocuments)
    {
        $form = $this->ifmsaFormsFactory->createPdfSelectionForm($personInfo, $cardOfDocuments);
        $form->setAction($this->link('Ifmsa:pdf'));
        return $form;
    }

    private function createPdfForm($pdfType, array $personInfo, array $cardOfDocuments)
    {
        $form = $this->ifmsaFormsFactory->createPdfForm($pdfType, $personInfo, $cardOfDocuments);
        $form->onSuccess[] = array($this, 'pdfFormSucceeded');
        return $form;
    }

    public function pdfFormSucceeded(App\Forms\MyForm $form, $values)
    {
        // all checks are done in primary callback
        $pdfType = $values->pdfType;
        $personInfo = $values->personInfo;
        $cardOfDocuments = $values->cardOfDocuments;

        $personInfo['departmentChosen'] = $values->departmentChosen;
        if ($values->departmentChosen == 'other') {
            $personInfo['departmentChosen'] = $values->otherDepartment;
        }

        $person = $this->ifmsaPersons->findByAfNumber($personInfo['afNumber']);
        $person->setDepartment($personInfo['departmentChosen']);

        if ($pdfType == 'contactPerson') {
            $personInfo['accommodation'] = $values->accommodation;
            $person->setAccommodation($values->accommodation);
            $this->ifmsaRemotePdfFactory->generateContactPersonPdf($personInfo, $cardOfDocuments);
        } elseif ($pdfType == 'department') {
            $this->ifmsaRemotePdfFactory->generateDepartmentPdf($personInfo);
        }

        $this->ifmsaPersons->flush();
    }

    private function redirectIfUserHasEmptyIfmsaCredentials()
    {
        $userProfile = $this->currentUser->getOfficersProfile();
        $ifmsaUsername = trim($userProfile->getIfmsaUsername());
        $ifmsaPassword = trim($userProfile->getIfmsaPassword());

        if (empty($ifmsaUsername) || empty($ifmsaPassword)) {
            $this->flashMessage("Please enter your credentials to www.ifmsa.org before visiting proper sections in CUK."
                    . " If you cannot do that, please contact your commanding officer to set it up for you.");
            $this->redirect("Homepage:");
        }
    }

    private function fetchListOfPersons($fetch)
    {
        // create right info and selection form for year and month
        $personListInfo = new App\Helpers\PersonListInfo(
            $this->httpRequest->getPost('year'),
            $this->httpRequest->getPost('month')
        );
        $this['yearMonthForm'] = $this->createComponentYearMonthForm(
            $personListInfo->year,
            $personListInfo->month
        );

        $personList = array();
        $afList = array();
        try {
            // First connection
            $fetch(
                $personListInfo->year - 1,
                $personListInfo->bottomLimit,
                $personListInfo->topLimit,
                $personList,
                $afList
            );
            // Second connection
            $fetch(
                $personListInfo->year,
                $personListInfo->bottomLimit,
                $personListInfo->topLimit,
                $personList,
                $afList
            );
        } catch (IfmsaConnectionException $e) {
            $msg = "Ifmsa.org connection error: {$e->getMessage()}";
            $this->forward("Ifmsa:connectionError", $msg);
        }

        // Sorting
        usort($personList, ['\App\Helpers\PersonEntry', 'cmp_PersonEntry']); // @phpstan-ignore-line
        $this->template->personList = $personList;
        $this->template->afList = $afList;

        // update or create entries in database about this person numbers
        foreach ($this->template->personList as $person) {  // @phpstan-ignore-line
            $ifmsaPerson = $this->ifmsaPersons->findByAfNumber($person->afNumber);
            if ($ifmsaPerson) {
                $ifmsaPerson->setConfirmationNumber($person->confirmationNumber);
                $this->ifmsaPersons->flush();
            } else {
                $ifmsaPerson = new IfmsaPerson($person->afNumber, $person->confirmationNumber);
                $this->ifmsaPersons->persist($ifmsaPerson);
            }
        }
    }

    public function actionIncomings()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("IfmsaRemote", "view")) {
            $this->error("Access Denied");
        }

        $this->fetchListOfPersons(function ($a, $b, $c, &$d, &$e) {
            $this->ifmsaConnectionHelper->fetchIncomings($a, $b, $c, $d, $e);
        });

        // assigned to cps
        $assigned = array();
        foreach ($this->template->personList as $person) {
            $assigned[$person->afNumber] = $this->cpAssignedAfs->findOneByAfNumber($person->afNumber);
        }
        $this->template->assigned = $assigned;
    }

    public function actionOutgoings()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("IfmsaRemote", "view")) {
            $this->error("Access Denied");
        }

        $this->fetchListOfPersons(function ($a, $b, $c, &$d, &$e) {
            $this->ifmsaConnectionHelper->fetchOutgoings($a, $b, $c, $d, $e);
        });
    }

    public function actionPerson($afNumber)
    {
        if (!$this->isLoggedIn() || !$afNumber ||
                !$this->user->isAllowed("IfmsaRemote", "view")) {
            $this->error("Access Denied");
        }

        $assigned = $this->cpAssignedAfs->findOneByAfNumber($afNumber);
        $person = $this->ifmsaPersons->findByAfNumber($afNumber);
        $confNumber = '';
        if ($person) {
            $confNumber = $person->getConfirmationNumber();
        }

        $personInfo = array();
        $cardOfDocuments = array();
        try {
            $this->ifmsaConnectionHelper->fetchPersonAf(htmlspecialchars($afNumber), $personInfo);
            $this->ifmsaConnectionHelper->fetchPersonCard(htmlspecialchars($afNumber), $personInfo, $cardOfDocuments);
            $this->ifmsaConnectionHelper->fetchPersonCC(htmlspecialchars($confNumber), $personInfo);
        } catch (IfmsaConnectionException $e) {
            $msg = "Ifmsa.org connection error: {$e->getMessage()}";
            $this->forward("Ifmsa:connectionError", $msg);
        }

        // update or create new ifmsa person in database
        if ($person) {
            $person->setConfirmationNumber($confNumber);
            $person->setFirstname($personInfo['name']);
            $person->setSurname($personInfo['surname']);
            $person->setEmail($personInfo['email']);
            $person->setPhoto($personInfo['jpgPath']);
            $person->setAfArrival($this->dateHelper->createDateOrDefault($personInfo['arrivalDate'])->typed);
        } else {
            $person = new IfmsaPerson(
                $afNumber,
                $confNumber,
                $personInfo['name'],
                $personInfo['surname'],
                $personInfo['email'],
                $personInfo['jpgPath'],
                $this->dateHelper->createDateOrDefault($personInfo['arrivalDate'])->typed
            );
            $this->ifmsaPersons->persist($person);
        }

        // create pdf selection form
        $this['pdfSelectionForm'] = $this->createComponentPdfSelectionForm($personInfo, $cardOfDocuments);

        $this->template->personInfo = $personInfo;
        $this->template->cardOfDocuments = $cardOfDocuments;
        $this->template->assignedCP = $assigned;
    }

    public function actionPdf()
    {
        if (!$this->request->isMethod('post') ||
                !$this->isLoggedIn() ||
                !$this->user->isAllowed("IfmsaRemote", "view")) {
            $this->error("Access Denied");
        }

        $pdfType = $this->request->getPost('pdfType');
        $personInfo = $this->request->getPost('personInfo');
        $cardOfDocuments = $this->request->getPost('cardOfDocuments');

        if (!$cardOfDocuments) {
            $cardOfDocuments = array();
        }

        if ((!is_array($personInfo) && !$personInfo instanceof Traversable) ||
                (!is_array($cardOfDocuments) && !$cardOfDocuments instanceof Traversable)) {
            $this->error('Data in wrong format');
        }

        if ($pdfType == 'contactPerson' || $pdfType == 'department') {
            $this['pdfForm'] = $this->createPdfForm($pdfType, $personInfo, $cardOfDocuments);
        } elseif ($pdfType == 'thirdParty') {
            $cp = $this->cpAssignedAfs->findOneByAfNumber($personInfo['afNumber']);
            $personInfo['contactPerson'] = '';
            if ($cp) {
                $personInfo['contactPerson'] = $cp->getUser()->getFirstname() . ' ' . $cp->getUser()->getSurname();
            }
            $this->ifmsaRemotePdfFactory->generateThirdPartyPdf($personInfo);
        } else {
            $this->error('Bad request');
        }

        $this->template->pdfType = $pdfType;
    }

    public function actionGenerateTable(array $afList)
    {
        if (!$this->isLoggedIn() || !$afList ||
                !$this->user->isAllowed("IfmsaRemote", "view")) {
            $this->error("Access Denied");
        }

        $content = $this->ifmsaRemoteTableFactory->createIfmsaRemoteTable($afList);
        $this->responseHelpers->setXlsxFileResponse($this->getHttpResponse(), 'table.xlsx');
        $this->presenter->sendResponse(new Nette\Application\Responses\TextResponse($content));
    }

    public function actionUploadOfficerInfo($id)
    {
        $officer = $this->users->findOfficerOrThrow($id);
        $profile = $officer->getOfficersProfile();
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("IfmsaRemote", "view") ||
                !$this->user->isAllowed("Users", "view") ||
                !$this->myAuthorizator->isAllowedUsers(
                    'view',
                    $officer->getId(),
                    $officer->getFaculty()->getId(),
                    $officer->getRole()
                )) {
            $this->error('Access Denied');
        }

        try {
            $this->ifmsaConnectionHelper->uploadCpInfo($officer, $profile);
            $this->flashMessage('Contact Person was uploaded to www.ifmsa.org, please check it there, to avoid future problems.');
        } catch (IfmsaConnectionException $e) {
            $msg = "Ifmsa.org connection error: {$e->getMessage()}";
            $this->forward("Ifmsa:connectionError", $msg);
        }

        $this->redirect('Officers:profile', $id);
    }

    public function actionConnectionError($msg)
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("IfmsaRemote", "view")) {
            $this->error("Access Denied");
        }

        $this->logger->log($msg, 'access');
        $this->template->errorMessage = $msg;
    }
}
