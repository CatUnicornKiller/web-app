<?php

namespace App\Forms;

use App\Presenters\BasePresenter;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\CheckboxList;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextArea;

/**
 * Reimplementation of nette application Form suitable for material-css
 * framework. Automatic rendering is somehow working, but it is recommended to
 * write fresh latte template for forms.
 *
 * @property BasePresenter $presenter
 */
class MyForm extends Form
{
    /**
     * Constructor.
     * @param ?Nette\ComponentModel\IContainer $parent
     * @param string $name identifier of the form
     */
    public function __construct(Nette\ComponentModel\IContainer $parent = null, $name = null)
    {
        parent::__construct($parent, $name);

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
     * @param string|null $caption label
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
     * Add list of checkboxes to the form and return it.
     * @param string $name identifier
     * @param string $label label
     * @param array $items checkbox list items
     * @return MyCheckboxList
     */
    public function addCheckboxList(string $name, $label = null, array $items = null): CheckboxList
    {
        return $this[$name] = new MyCheckboxList($label, $items);
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

    /**
     * Add container to the form and return it.
     * @param string $name identifier
     * @return MyContainer
     */
    public function addContainer($name): Nette\Forms\Container
    {
        $control = new MyContainer;
        $control->currentGroup = $this->currentGroup;
        return $this[$name] = $control;
    }
}
