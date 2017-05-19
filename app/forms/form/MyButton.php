<?php

namespace App\Forms;

use Nette\Forms\Controls\Button;

/**
 * Reimplementation of nette Button more suitable for material-css framework.
 */
class MyButton extends Button
{
    /**
     * Contructor with label name.
     * @param type $caption
     */
    public function __construct($caption = null)
    {
        parent::__construct($caption);
    }

    /**
     * Get ready-to-render html structure for the button control part.
     * @return \Nette\Utils\Html
     */
    public function getControl()
    {
        $control = parent::getControl();
        $control->addAttributes(array('class' => 'btn waves-effect waves-light'));
        return $control;
    }
}
