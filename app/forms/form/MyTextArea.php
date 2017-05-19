<?php

namespace App\Forms;

use Nette\Forms\Controls\TextArea;

/**
 * Reimplementation of nette TextArea more suitable for material-css framework.
 */
class MyTextArea extends TextArea
{
    /**
     * Constructor with label description.
     * @param string $label
     */
    public function __construct($label = null)
    {
        parent::__construct($label);
    }

    /**
     * Get ready-to-render html structure for the textarea control part.
     * @return \Nette\Utils\Html
     */
    public function getControl()
    {
        $control = parent::getControl();
        $control->addAttributes(array('class' => 'materialize-textarea'));
        return $control;
    }
}
