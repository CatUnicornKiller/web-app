<?php

namespace App\Forms;

use App;
use Nette;
use App\Model\Repository\IfmsaPersons;

/**
 * Class containing factory methods for forms mainly concerning ifmsa.
 * Alongside factories there can also be success callbacks.
 */
class IfmsaFormsFactory extends Nette\Object
{
    /** @var App\Helpers\StringHelper */
    private $stringHelpers;
    /** @var App\Users\MyAuthorizator */
    private $myAuthorizator;
    /** @var IfmsaPersons */
    private $ifmsaPersons;

    /**
     * DI Constructor.
     * @param App\Helpers\StringHelper $stringHelpers
     * @param App\Users\MyAuthorizator $myAuthorizator
     * @param IfmsaPersons $ifmsaPersons
     */
    public function __construct(
        App\Helpers\StringHelper $stringHelpers,
        App\Users\MyAuthorizator $myAuthorizator,
        IfmsaPersons $ifmsaPersons
    ) {

        $this->stringHelpers = $stringHelpers;
        $this->myAuthorizator = $myAuthorizator;
        $this->ifmsaPersons = $ifmsaPersons;
    }

    /**
     * Create filtering form for the outgoings or incomings lists.
     * @param int $year
     * @param int $month
     * @return \App\Forms\MyForm
     */
    public function createYearMonthForm($year, $month)
    {
        $form = new MyForm;
        $yearArr = array();
        for ($i = (intval(date('Y')) + 1); $i >= 2013; $i--) {
            $yearArr[$i] = $i;
        }
        $form->addSelect('year', 'Year', $yearArr);
        $monthArr = array(
            '1' => 'January',
            '2' => 'February',
            '3' => 'March',
            '4' => 'April',
            '5' => 'May',
            '6' => 'June',
            '7' => 'July',
            '8' => 'August',
            '9' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        );
        $form->addSelect('month', 'Month', $monthArr)
                ->setPrompt('All');
        $form->addSubmit('send', 'Show results');

        try {
            $form->setDefaults(array(
                'year' => $year,
                'month' => $month
            ));
        } catch (\Exception $e) {
        }

        return $form;
    }

    /**
     * Create PDF type selection form.
     * @param array $personInfo person information obtained from ifmsa.org
     * @param array $cardOfDocuments list of person documents obtainer from
     * ifmsa.org
     * @return \App\Forms\MyForm
     */
    public function createPdfSelectionForm(array $personInfo, array $cardOfDocuments)
    {
        $form = new MyForm();
        $form->addRadioList('pdfType', 'PDF Type', array(
            'contactPerson' => 'Contact Person',
            'department' => 'Department',
            'thirdParty' => 'Third Party'
        ))->setRequired('PDF type must be selected');
        $form->addSubmit('send', 'Generate');
        $pi = $form->addContainer('personInfo');
        foreach ($personInfo as $key => $value) {
            if (is_scalar($value)) {
                $pi->addHidden($this->stringHelpers->alphaNumText($key), $value);
            }
        }
        $cod = $form->addContainer('cardOfDocuments');
        foreach ($cardOfDocuments as $key => $value) {
            if (is_scalar($value)) {
                $cod->addHidden($this->stringHelpers->alphaNumText($key), $value);
            }
        }
        return $form;
    }

    /**
     * Create PDF initialization form. Some additional information can be added
     * here.
     * @param string $pdfType
     * @param array $personInfo person information obtained from ifmsa.org
     * @param array $cardOfDocuments list of person documents obtainer from
     * ifmsa.org
     * @return \App\Forms\MyForm
     */
    public function createPdfForm($pdfType, array $personInfo, array $cardOfDocuments)
    {
        $form = new MyForm();
        $depStr = 'Department';
        if (!$this->myAuthorizator->isScope()) {
            $depStr = 'Project';
        }
        $ifmsaPerson = $this->ifmsaPersons->findByAfNumber($personInfo['afNumber']);

        if ($pdfType == 'contactPerson' || $pdfType == 'department') {
            $deps = array(
                $personInfo['department1'] => $this->stringHelpers->getDepartmentDescription($personInfo['department1']),
                $personInfo['department2'] => $this->stringHelpers->getDepartmentDescription($personInfo['department2']),
                $personInfo['department3'] => $this->stringHelpers->getDepartmentDescription($personInfo['department3'])
            );
            if (strlen($personInfo['department4']) > 0) {
                $deps[$personInfo['department4']] = $this->stringHelpers->getDepartmentDescription($personInfo['department4']);
            }
            $deps['other'] = '';
            $depRad = $form->addRadioList('departmentChosen', 'Choose ' . $depStr, $deps);
            $depOther = $form->addText('otherDepartment');

            // load saved department from database and try to save it as default
            try {
                $depRad->setDefaultValue($ifmsaPerson->department);
            } catch (\Exception $e) {
                $depRad->setDefaultValue('other');
                $depOther->setDefaultValue($ifmsaPerson->department);
            }

            if ($pdfType == 'contactPerson') {
                $form->addHidden('pdfType', 'contactPerson');
                $form->addTextArea('accommodation', 'Accommodation')
                    ->setDefaultValue($ifmsaPerson->accommodation);
            } else {
                $form->addHidden('pdfType', 'department');
            }
        }

        $pi = $form->addContainer('personInfo');
        foreach ($personInfo as $key => $value) {
            if (is_scalar($value)) {
                $pi->addHidden($this->stringHelpers->alphaNumText($key), $value);
            }
        }
        $cod = $form->addContainer('cardOfDocuments');
        foreach ($cardOfDocuments as $key => $value) {
            if (is_scalar($value)) {
                $cod->addHidden($this->stringHelpers->alphaNumText($key), $value);
            }
        }

        $form->addSubmit('send', 'Generate PDF');
        $form->onSuccess[] = array($this, 'pdfFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the PDF initialization form.
     * @param \App\Forms\MyForm $form
     * @param array $values
     */
    public function pdfFormSucceeded(MyForm $form, $values)
    {
        if ($values->pdfType != 'contactPerson' &&
                $values->pdfType != 'department') {
            $form->addError('Bad request');
        } elseif ((!is_array($values->personInfo) && !$values->personInfo instanceof \Traversable) ||
                (!is_array($values->cardOfDocuments) && !$values->cardOfDocuments instanceof \Traversable)) {
            $form->addError('Data in bad format');
        }
    }
}
