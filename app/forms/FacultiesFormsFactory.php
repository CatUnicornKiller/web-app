<?php

namespace App\Forms;

use App\Model\Entity\Faculty;
use App\Model\Repository\Faculties;
use Nette;
use Nette\Application\UI\Form;

/**
 * Class containing factory methods for forms mainly concerning faculties
 * management. Alongside factories there can also be success callbacks.
 */
class FacultiesFormsFactory
{
    use Nette\SmartObject;

    /** @var Faculties */
    private $faculties;

    /**
     * DI Constructor.
     * @param Faculties $faculties
     */
    public function __construct(Faculties $faculties)
    {
        $this->faculties = $faculties;
    }

    /**
     * Create base faculty form for the further usage.
     * @return MyForm
     */
    private function createFacultyForm()
    {
        $form = new MyForm();
        $form->addText('facultyName', 'Faculty Name')
                ->setRequired('Faculty Name was not filled')
                ->addRule(Form::MAX_LENGTH, 'Faculty Name is too long', 500)
                ->setHtmlAttribute('length', 500);
        $form->addText('facultyAddress', 'Faculty Address')
                ->setRequired('Faculty Address was not filled')
                ->addRule(Form::MAX_LENGTH, 'Faculty Address is too long', 1000)
                ->setHtmlAttribute('length', 1000);
        $form->addText('facultyShortcut', 'Faculty Shortcut')
                ->setRequired('Faculty Shortcut was not filled')
                ->addRule(Form::MAX_LENGTH, 'Faculty Shortcut is too long', 20)
                ->setHtmlAttribute('length', 20);
        $form->addText('ifmsaLcNumber', 'Local Committee number (as stated in www.ifmsa.org)')
                ->setHtmlType('number')
                ->setRequired('Local Committee number is required')
                ->addRule(Form::INTEGER, 'Local Committee has to be number');
        return $form;
    }

    /**
     * Create add faculty form.
     * @return MyForm
     */
    public function createAddFacultyForm()
    {
        $form = $this->createFacultyForm();
        $form->addSubmit('send', 'Add Faculty');
        $form->onSuccess[] = array($this, 'addFacultyFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the add faculty form.
     * @param MyForm $form
     * @param object $values
     */
    public function addFacultyFormSucceeded(MyForm $form, $values)
    {
        if ($this->faculties->findByName($values->facultyName)) {
            $form->addError('Faculty with this name already exists');
            return;
        }

        $faculty = new Faculty(
            $values->facultyName,
            $values->facultyAddress,
            $values->facultyShortcut,
            $values->ifmsaLcNumber
        );
        $this->faculties->persist($faculty);
    }

    /**
     * Create edit faculty form.
     * @return MyForm
     */
    public function createEditFacultyForm()
    {
        $form = $this->createFacultyForm();
        $form->addHidden('id');
        $form->addSubmit('send', 'Edit Faculty');
        $form->onSuccess[] = array($this, 'editFacultyFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the edit faculty form.
     * @param MyForm $form
     * @param object $values
     */
    public function editFacultyFormSucceeded(MyForm $form, $values)
    {
        $faculties = $this->faculties->findByName($values->facultyName);
        if (count($faculties) > 0 && (count($faculties) > 1 ||
                current($faculties)->getId() != $values->id)) {
            $form->addError('Faculty with this name already exists');
            return;
        }

        $faculty = $this->faculties->findOrThrow($values->id);
        $faculty->setFacultyName($values->facultyName);
        $faculty->setFacultyAddress($values->facultyAddress);
        $faculty->setFacultyShortcut($values->facultyShortcut);
        $faculty->setIfmsaLcNumber($values->ifmsaLcNumber);
        $this->faculties->flush();
    }
}
