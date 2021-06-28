<?php

namespace App\Forms;

use Nette\Forms\Controls\Button;
use Nette\Utils\Html;

/**
 * Reimplementation of nette Button more suitable for material-css framework.
 */
class MyButton extends Button
{
    /**
     * Contructor with label name.
     * @param string|null $caption
     */
    public function __construct($caption = null)
    {
        parent::__construct($caption);
    }

    /**
     * Get ready-to-render html structure for the button control part.
     * @return Html
     */
    public function getControl($caption = null): Html
    {
        $control = parent::getControl();
        $control->addAttributes(array('class' => 'btn waves-effect waves-light'));
        return $control;
    }
}
