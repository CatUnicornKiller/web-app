<?php

namespace App\Menu;

/**
 * One menu item holder, can have sub-items which should be of the same type.
 */
class MenuItem
{
    /**
     * Holder construction.
     * @param bool $active
     * @param string $name
     * @param string $link
     * @param array $subItems
     */
    public function __construct($active, $name, $link, array $subItems = [])
    {
        $this->active = $active;
        $this->name = $name;
        $this->link = $link;
        $this->subItems = $subItems;
    }

    /** True if item should be marked as active in html. */
    public $active;
    /** Textual description of the page. */
    public $name;
    /** Link to the page. */
    public $link;
    /** List of MenuItem which are logically under this item. */
    public $subItems;
}
