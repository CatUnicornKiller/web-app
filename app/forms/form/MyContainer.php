<?php

namespace App\Forms;

use Nette;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextArea;

/**
 * Reimplementation of nette Container suitable for material-css framework.
 * Used in MyForm and MySimpleForm.
 */
class MyContainer extends Nette\Forms\Container
{
    /**
     * Add checkbox to the form and return it.
     * @param string $name identifier
     * @param string $caption label
     * @return MyCheckbox
     */
    public function addCheckbox(string $name, $caption = null): Checkbox
    {
        return $this[$name] = new MyCheckbox($caption);
    }

    /**
     * Add button to the form and return it.
     * @param string $name identifier
     * @param string $caption label
     * @return MyButton
     */
    public function addButton(string $name, $caption = null): Button
    {
        return $this[$name] = new MyButton($caption);
    }

    /**
     * Add submit button to the form and return it.
     * @param string $name identifier
     * @param string $caption label
     * @return MySubmitButton
     */
    public function addSubmit(string $name, $caption = null): SubmitButton
    {
        return $this[$name] = new MySubmitButton($caption);
    }

    /**
     * Add radio list to the form and return it.
     * @param string $name identifier
     * @param string $label label
     * @param array $items radio list items
     * @return MyRadioList
     */
    public function addRadioList(string $name, $label = null, array $items = null): RadioList
    {
        return $this[$name] = new MyRadioList($label, $items);
    }

    /**
     * Add text area to the form and return it.
     * @param string $name identifier
     * @param string $label label
     * @param int|null $cols columns
     * @param int|null $rows rows
     * @return MyTextArea
     */
    public function addTextArea(string $name, $label = null, int $cols = null, int $rows = null): TextArea
    {
        $control = new MyTextArea($label);
        $control->setHtmlAttribute('cols', $cols)
            ->setHtmlAttribute('rows', $rows);
        return $this[$name] = $control;
    }

    /**
     * Add nette Form original text area to the form and return it.
     * @param string $name identifier
     * @param string $label label
     * @param int $cols columns
     * @param int $rows rows
     * @return TextArea
     */
    public function addOriginalTextArea(string $name, $label = null, $cols = null, $rows = null)
    {
        return parent::addTextArea($name, $label, $cols, $rows);
    }
}
