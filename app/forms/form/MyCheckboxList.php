<?php

namespace App\Forms;

use Nette;
use Nette\Forms\Controls\CheckboxList;
use Nette\Utils\Html;

/**
 * Reimplementation of nette CheckboxList more suitable for material-css
 * framework.
 */
class MyCheckboxList extends CheckboxList
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
     * Get ready-to-render html structure for the checkbox list control part.
     * @return Html
     */
    public function getControl(): Html
    {
        $out = new Html();

        $items = $this->getItems();
        foreach ($items as $key => $value) {
            $out->addHtml($this->getControlPart($key));
            $out->addHtml($this->getLabelPart($key));
            $out->addHtml($this->separator);
        }

        return $out;
    }
}
