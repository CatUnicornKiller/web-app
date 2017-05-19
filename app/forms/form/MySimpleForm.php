<?php

namespace App\Forms;

use Nette\Forms\Form;

/**
 * Reimplementation of nette basic Form suitable for material-css framework.
 * Automatic rendering is somehow working, but it is recommended to write fresh
 * latte template for forms.
 */
class MySimpleForm extends Form
{
    /**
     * Constructor.
     * @param string $name identifier of the form
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        $renderer = $this->getRenderer();
        $renderer->wrappers['error']['container'] = 'ul class="collection form-error"';
        $renderer->wrappers['error']['item'] = 'li class="collection-item red darken-3 white-text"';
        $renderer->wrappers['controls']['container'] = 'div class="row"';
        $renderer->wrappers['pair']['container'] = 'div class="input-field col s12"';
        $renderer->wrappers['control']['container'] = null;
        $renderer->wrappers['label']['container'] = null;
    }

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

    /**
     * Add container to the form and return it.
     * @param string $name identifier
     * @return MyContainer
     */
    public function addContainer($name)
    {
        $control = new MyContainer;
        $control->currentGroup = $this->currentGroup;
        return $this[$name] = $control;
    }
}
