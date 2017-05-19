<?php

namespace App\Forms;

use Nette;
use Nette\Forms\Controls\RadioList;

/**
 * Reimplementation of nette RadioList more suitable for material-css framework.
 */
class MyRadioList extends RadioList
{
    /**
     * Constructor with label description and list items.
     * @param string $label
     * @param array $items
     */
    public function __construct($label = null, array $items = null)
    {
        parent::__construct($label, $items);
    }

    /**
     * Label is ommited.
     * @param string $caption
     * @return \Nette\Utils\Html
     */
    public function getLabel($caption = null)
    {
        return null;
    }

    /**
     * Get ready-to-render html structure for the radio list control part.
     * @return \Nette\Utils\Html
     */
    public function getControl()
    {
        $out = new Nette\Utils\Html();

        $items = $this->getItems();
        foreach ($items as $key => $value) {
            $out->add($this->getControlPart($key));
            $out->add($this->getLabelPart($key));
            $out->add($this->separator);
        }

        return $out;
    }
}
