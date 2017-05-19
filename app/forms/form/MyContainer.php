<?php

namespace App\Forms;

use Nette;

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
    public function addCheckbox($name, $caption = null)
    {
        return $this[$name] = new MyCheckbox($caption);
    }

    /**
     * Add button to the form and return it.
     * @param string $name identifier
     * @param string $caption label
     * @return MyButton
     */
    public function addButton($name, $caption = null)
    {
        return $this[$name] = new MyButton($caption);
    }

    /**
     * Add submit button to the form and return it.
     * @param string $name identifier
     * @param string $caption label
     * @return MySubmitButton
     */
    public function addSubmit($name, $caption = null)
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
    public function addRadioList($name, $label = null, array $items = null)
    {
        return $this[$name] = new MyRadioList($label, $items);
    }

    /**
     * Add text area to the form and return it.
     * @param string $name identifier
     * @param string $label label
     * @param int $cols columns
     * @param int $rows rows
     * @return MyTextArea
     */
    public function addTextArea($name, $label = null, $cols = null, $rows = null)
    {
        $control = new MyTextArea($label);
        $control->setAttribute('cols', $cols)->setAttribute('rows', $rows);
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
    public function addOriginalTextArea($name, $label = null, $cols = null, $rows = null)
    {
        return parent::addTextArea($name, $label, $cols, $rows);
    }
}
