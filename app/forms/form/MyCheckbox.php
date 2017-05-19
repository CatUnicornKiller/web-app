<?php

namespace App\Forms;

use Nette\Utils\Html;
use Nette\Forms\Controls\Checkbox;

/**
 * Reimplementation of nette Checkbox more suitable for material-css framework.
 */
class MyCheckbox extends Checkbox
{
    /**
     * Constructor with label textual description.
     * @param string $label
     */
    public function __construct($label = null)
    {
        parent::__construct($label);
    }

    /**
     * Get ready-to-render html structure for the checkbox control part.
     * @return Html
     */
    public function getControl()
    {
        $c = new Html();
        $c->add($this->getControlPart());
        $c->add($this->getLabelPart());
        return $c;
    }
}
