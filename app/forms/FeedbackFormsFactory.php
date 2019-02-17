<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App;
use App\Users\UserManager;
use App\Model\Entity\User;
use App\Model\Entity\Feedback;
use App\Model\Repository\Faculties;
use App\Model\Repository\Countries;
use App\Model\Repository\FeedbackRepository;

/**
 * Class containing factory methods for forms mainly concerning feedback.
 * Alongside factories there can also be success callbacks.
 */
class FeedbackFormsFactory
{
    use Nette\SmartObject;

    /** @var User */
    private $user;
    /** @var Faculties */
    private $faculties;
    /** @var Countries */
    private $countries;
    /** @var FeedbackRepository */
    private $feedbackRepository;
    /** @var App\Helpers\FeedbackHelper */
    private $feedbackHelpers;
    /** @var App\Helpers\HumanDetectorFormHelper */
    private $humanDetector;

    /**
     * DI Constructor.
     * @param UserManager $userManager
     * @param Faculties $faculties
     * @param Countries $countries
     * @param FeedbackRepository $feedbackRepository
     * @param App\Helpers\FeedbackHelper $feedbackHelpers
     * @param App\Helpers\HumanDetectorFormHelper $humanDetector
     */
    public function __construct(
        UserManager $userManager,
        Faculties $faculties,
        Countries $countries,
        FeedbackRepository $feedbackRepository,
        App\Helpers\FeedbackHelper $feedbackHelpers,
        App\Helpers\HumanDetectorFormHelper $humanDetector
    ) {

        $this->user = $userManager->getCurrentUser();
        $this->faculties = $faculties;
        $this->countries = $countries;
        $this->feedbackRepository = $feedbackRepository;
        $this->feedbackHelpers = $feedbackHelpers;
        $this->humanDetector = $humanDetector;
    }

    /**
     * Get countries list for the selection box.
     * @return array
     */
    private function getCountriesSelect()
    {
        $countries = $this->countries->getIfmsaCountries();
        $countArr = array();
        foreach ($countries as $country) {
            $countArr[$country->id] = $country->countryName;
        }
        return $countArr;
    }

    /**
     * Get score/scope list for the selection box.
     * @return array
     */
    public function getScoreScopeSelect()
    {
        return array('scope' => 'SCOPE', 'score' => 'SCORE');
    }

    /**
     * Create add feedback form.
     * @param int $id country identification
     * @return \App\Forms\MyForm
     */
    public function createAddFeedbackForm($id)
    {
        $form = new MyForm;

        $form->addText('name', $this->feedbackHelpers->getItemDescription('name'))
                ->setRequired('Name of person is required')
                ->addRule(Form::MAX_LENGTH, 'Name is too long', 255)
                ->setAttribute('length', 255);
        $form->addText('grade', $this->feedbackHelpers->getItemDescription('grade'))
                ->setType('number')->setDefaultValue(1)
                ->setRequired('Year of Study is required')
                ->addRule(Form::INTEGER, 'Year of Study is in bad format');
        $form->addSelect('hostCountry', $this->feedbackHelpers->getItemDescription('hostCountry'), $this->getCountriesSelect())
                ->setRequired('Host Country is required');
        $form->addText('hostCity', $this->feedbackHelpers->getItemDescription('hostCity'))
                ->setRequired('Host City is required')
                ->addRule(Form::MAX_LENGTH, 'Host City is too long', 255)
                ->setAttribute('length', 255);
        $form->addText('hostFaculty', $this->feedbackHelpers->getItemDescription('hostFaculty'))
                ->setRequired('Host Faculty is required')
                ->addRule(Form::MAX_LENGTH, 'Host Faculty is too long', 255)
                ->setAttribute('length', 255);
        $form->addText('hostDepartment', $this->feedbackHelpers->getItemDescription('hostDepartment'))
                ->setRequired('Host Department is required')
                ->addRule(Form::MAX_LENGTH, 'Host Department is too long', 255)
                ->setAttribute('length', 255);
        $form->addText('startDate', $this->feedbackHelpers->getItemDescription('startDate'))
                ->setRequired('Start Date is required')
                ->setAttribute('class', 'datepicker')
                ->setValue(date('j. n. Y'));
        $form->addText('endDate', $this->feedbackHelpers->getItemDescription('endDate'))
                ->setRequired('End Date is required')
                ->setAttribute('class', 'datepicker')
                ->setValue(date('j. n. Y'));
        $form->addSelect(
            'exchangeType',
            $this->feedbackHelpers->getItemDescription('exchangeType'),
            $this->getScoreScopeSelect()
        )
                ->setRequired('Exchange type is required');
        $form->addSelect(
            'preparationVisa',
            $this->feedbackHelpers->getItemDescription('preparationVisa'),
            array(0 => 'No', 1 => 'Yes')
        )
                ->setRequired('Visa answer is required');
        $form->addSelect(
            'preparationVaccination',
            $this->feedbackHelpers->getItemDescription('preparationVaccination'),
            array(0 => 'No', 1 => 'Yes')
        )
                ->setRequired('Vaccination answer is required');
        $form->addTextArea('preparationComplications', $this->feedbackHelpers->getItemDescription('preparationComplications'))
                ->setRequired('Preparation complications answer is required')
                ->addRule(Form::MAX_LENGTH, 'Preparation complications is too long', 10000)
                ->setAttribute('length', 10000);
        $form->addTextArea('preparationMoney', $this->feedbackHelpers->getItemDescription('preparationMoney'))
                ->setRequired('Preparation money answer is required')
                ->addRule(Form::MAX_LENGTH, 'Preparation money is too long', 10000)
                ->setAttribute('length', 10000);
        $form->addTextArea('accommodation', $this->feedbackHelpers->getItemDescription('accommodation'))
                ->setRequired('Accommodation answer is required')
                ->addRule(Form::MAX_LENGTH, 'Accommodation is too long', 10000)
                ->setAttribute('length', 10000);
        $form->addTextArea('cpHelp', $this->feedbackHelpers->getItemDescription('cpHelp'))
                ->setRequired('CP Help answer is required')
                ->addRule(Form::MAX_LENGTH, 'CP Help is too long', 10000)
                ->setAttribute('length', 10000);
        $form->addTextArea('exchangeCommunication', $this->feedbackHelpers->getItemDescription('exchangeCommunication'))
                ->setRequired('Exchange communication answer is required')
                ->addRule(Form::MAX_LENGTH, 'Exchange communication is too long', 10000)
                ->setAttribute('length', 10000);
        $form->addTextArea('socialTravelling', $this->feedbackHelpers->getItemDescription('socialTravelling'))
                ->setRequired('Travelling answer is required')
                ->addRule(Form::MAX_LENGTH, 'Social travelling is too long', 10000)
                ->setAttribute('length', 10000);
        $form->addTextArea('socialProgram', $this->feedbackHelpers->getItemDescription('socialProgram'))
                ->setRequired('Social program answer is required')
                ->addRule(Form::MAX_LENGTH, 'Social program is too long', 10000)
                ->setAttribute('length', 10000);
        $form->addTextArea('furtherTips', $this->feedbackHelpers->getItemDescription('furtherTips'))
                ->setRequired('Further tips answer is required')
                ->addRule(Form::MAX_LENGTH, 'Further tips is too long', 10000)
                ->setAttribute('length', 10000);
        $form->addTextArea('overallReview', $this->feedbackHelpers->getItemDescription('overallReview'))
                ->setRequired('Overall Review is required')
                ->addRule(Form::MAX_LENGTH, 'Overall Review is too long', 10000)
                ->setAttribute('length', 10000);

        try {
            $defs = array('hostCountry' => $id);
            if ($this->user) {
                $defs['name'] = $this->user->firstname . ' ' . $this->user->surname;
            }
            $form->setDefaults($defs);
        } catch (\Exception $e) {
        }

        $this->humanDetector->addToForm($form);
        $form->addSubmit('send', 'Add Feedback');
        $form->onSuccess[] = array($this, 'addFeedbackFormSucceeded');
        return $form;
    }

    /**
     * Check add feedback form for the errors.
     * @param MyForm $form
     * @param array $values
     * @return boolean true if form is without errors
     */
    private function checkAddFeedbackForm($form, $values)
    {
        $startDate = date_create_from_format("j. n. Y", $values->startDate);
        $endDate = date_create_from_format("j. n. Y", $values->endDate);
        if ($startDate === false) {
            $form->addError('Bad format of start date');
            return false;
        } elseif ($endDate === false) {
            $form->addError('Bad format of end date');
            return false;
        } elseif (!$this->humanDetector->checkForm($form, $values)) {
            return false;
        }

        return true;
    }

    /**
     * Success callback for the add feedback form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function addFeedbackFormSucceeded(MyForm $form, $values)
    {
        if (!$this->checkAddFeedbackForm($form, $values)) {
            return;
        }

        $preparationVisa = 0;
        $preparationVaccination = 0;
        if ($values->preparationVisa == 1) {
            $preparationVisa = 1;
        }
        if ($values->preparationVaccination == 1) {
            $preparationVaccination = 1;
        }

        $country = $this->countries->findOrThrow($values->hostCountry);
        $startDate = date_create_from_format("j. n. Y", $values->startDate);
        $endDate = date_create_from_format("j. n. Y", $values->endDate);

        $feedback = new Feedback(
            $values->name,
            $values->grade,
            $country,
            $values->hostCity,
            $values->hostFaculty,
            $values->hostDepartment,
            $startDate,
            $endDate,
            $values->exchangeType,
            $preparationVisa,
            $preparationVaccination,
            $values->preparationComplications,
            $values->preparationMoney,
            $values->accommodation,
            $values->cpHelp,
            $values->exchangeCommunication,
            $values->socialTravelling,
            $values->socialProgram,
            $values->furtherTips,
            $values->overallReview
        );
        $this->feedbackRepository->persist($feedback);

        $form->presenter->flashMessage('Feedback was successfully added');
        $form->presenter->redirect('Feedback:feedbackDetail', $feedback->id);
    }

    /**
     * Get years list for the selection box.
     * @return array
     */
    private function getContractsYears()
    {
        $res = array();
        $current = date('Y');
        for ($i = 2015; $i <= ($current + 1); ++$i) {
            $res[$i] = $i . '/' . ($i + 1);
        }
        return $res;
    }

    /**
     * Get yes/no list for the selection box.
     * @return array
     */
    private function getYesNo()
    {
        return array(0 => 'No', 1 => 'Yes');
    }

    /**
     * Create feedback countries management form.
     * @return \App\Forms\MyForm
     */
    public function createCountriesManagementForm()
    {
        $form = new MyForm;

        $form->addSelect('year', 'Contracts Year', $this->getContractsYears())
                ->setRequired('Year is required')->setDefaultValue(date('Y'));

        $isIfmsa = $form->addContainer('isIfmsa');
        $clinicalContracts = $form->addContainer('clinicalContracts');
        $researchContracts = $form->addContainer('researchContracts');

        $countries = $this->countries->findAll();
        foreach ($countries as $country) {
            $isIfmsa->addSelect($country->id, 'Choose Country', $this->getYesNo())
                    ->setDefaultValue($country->isIfmsa);
            $clinicalContracts->addText($country->id, '')
                    ->setType('number')
                    ->setDefaultValue($country->ifmsaClinicalContracts)
                    ->addRule(Form::INTEGER, 'Clinical contracts has to be integer')
                    ->setRequired('Clinical contracts are required');
            $researchContracts->addText($country->id, '')
                    ->setType('number')
                    ->setDefaultValue($country->ifmsaResearchContracts)
                    ->addRule(Form::INTEGER, 'Research contracts has to be integer')
                    ->setRequired('Research contracts are required');
        }

        $form->addSubmit('send', 'Save');
        $form->onSuccess[] = array($this, 'countriesManagementFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the countries management form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function countriesManagementFormSucceeded(MyForm $form, $values)
    {
        $year = $values->year;

        foreach ($values->isIfmsa as $id => $val) {
            $isIfmsa = 0;
            if ($val == 1) {
                $isIfmsa = 1;
            }
            $clinicalContracts = $values->clinicalContracts[$id];
            $researchContracts = $values->researchContracts[$id];

            $country = $this->countries->findOrThrow($id);
            $country->isIfmsa = $isIfmsa;
            $country->ifmsaClinicalContracts = $clinicalContracts;
            $country->ifmsaResearchContracts = $researchContracts;
            $country->ifmsaContractsYear = $year;
            $this->countries->flush();
        }
    }

    /**
     * Create feedback filtering form. Return simple form with GET method set.
     * @param string $exchangeType
     * @param string $startDate
     * @param string $endDate
     * @param string $hostCity
     * @param string $hostFaculty
     * @return \App\Forms\MySimpleForm
     */
    public function createFilterFeedbackForm(
        $exchangeType,
        $startDate,
        $endDate,
        $hostCity,
        $hostFaculty
    ) {

        $form = new MySimpleForm();
        $form->setMethod('get');

        $score_scope = $this->getScoreScopeSelect();
        $exchange_type_select = array('' => 'All') + $score_scope;
        $form->addSelect(
            'exchangeType',
            $this->feedbackHelpers->getItemDescription('exchangeType'),
            $exchange_type_select
        );

        $form->addText('startDate', $this->feedbackHelpers->getItemDescription('startDate'));
        $form->addText('endDate', $this->feedbackHelpers->getItemDescription('endDate'));
        $form->addText('hostCity', $this->feedbackHelpers->getItemDescription('hostCity'));
        $form->addText('hostFaculty', $this->feedbackHelpers->getItemDescription('hostFaculty'));

        $form->addSubmit('send', 'Filter');

        try {
            $form->setDefaults(array('exchangeType' => $exchangeType,
                'hostCity' => $hostCity,
                'hostFaculty' => $hostFaculty,
                'startDate' => $startDate,
                'endDate' => $endDate));
        } catch (\Exception $e) {
        }

        return $form;
    }

    /**
     * Create feedback list filter form. Return simple form with GET method set.
     * @param int $country
     * @param string $startDate
     * @param string $endDate
     * @return \App\Forms\MySimpleForm
     */
    public function createFilterFeedbackListForm($country, $startDate, $endDate)
    {
        $form = new MySimpleForm;
        $form->setMethod('get');

        $form->addSelect('country', $this->feedbackHelpers->getItemDescription('hostCountry'), $this->getCountriesSelect())
                ->setPrompt('All');

        $form->addText('startDate', $this->feedbackHelpers->getItemDescription('startDate'));
        $form->addText('endDate', $this->feedbackHelpers->getItemDescription('endDate'));

        $form->addSubmit('send', 'Filter');

        try {
            $form->setDefaults(array(
                'startDate' => $startDate,
                'endDate' => $endDate,
                'country' => $country
            ));
        } catch (\Exception $e) {
        }

        return $form;
    }
}
