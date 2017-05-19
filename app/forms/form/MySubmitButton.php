<?php

namespace App\Forms;

use Nette\Forms\Controls\SubmitButton;

/**
 * Reimplementation of nette SubmitButton more suitable for material-css
 * framework.
 */
class MySubmitButton extends SubmitButton
{
    /**
     * Constructor with button description.
     * @param string $caption
     */
    public function __construct($caption = null)
    {
        parent::__construct($caption);
    }

    /**
     * Get ready-to-render html structure for the submit button control part.
     * @param type $caption
     * @return \Nette\Utils\Html
     */
    public function getControl($caption = null)
    {
        $control = parent::getControl($caption);
        $control->addAttributes(array('class' => 'btn waves-effect waves-light'));
        return $control;
    }
}
