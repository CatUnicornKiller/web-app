<?php

namespace App\Menu;

use Nette;

/**
 * Menu rendering control.
 */
class MenuControl extends Nette\Application\UI\Control
{
    /**
     * Create and return all items which should be visible in the sidebar.
     * @return array
     */
    protected function constructItems()
    {
        $items = array();
        if (!$this->presenter->user->isLoggedIn()) {
            $items['Homepage'] = 'Homepage:default';
            $items['Login'] = 'Homepage:login';
            $items['Registration of Incomings'] = 'Homepage:incomingsRegistration';
            $items['Registration of Officers'] = 'Homepage:registration';
            $items['Feedback'] = 'Feedback:';
            $items['Showroom'] = 'Showroom:';
        }
        if ($this->presenter->user->isLoggedIn()) {
            $items['Dashboard'] = 'Homepage:dashboard';
            $items['FAQ'] = 'Homepage:faq';

            if ($this->presenter->user->isAllowed('IncomingsMenu', 'view')) {
                $items['My Incoming Profile'] = 'Incomings:profile';
                $items['General Information'] = 'Incomings:generalInformation';
                $items['Faculty Information'] = 'Incomings:facultyInformation';
                $items['IFMSA Hierarchy'] = 'Incomings:ifmsaHierarchy';
                $items['Events'] = 'Events:';
            }

            if ($this->presenter->user->isAllowed('ContactPersonsMenu', 'view')) {
                $items["CP Section"]['CP Introduction'] = 'ContactPersons:introduction';
                $items["CP Section"]['My Profile'] = 'ContactPersons:profile';
                $items["CP Section"]['My Tasks'] = 'Tasks:overview';
            }

            if ($this->presenter->user->isAllowed('ContactPersonsMenu', 'view')) {
                $items["Events"]['Add Event'] = 'Events:addEvent';
                $items["Events"]['Events Calendar'] = 'Events:';
            }

            if ($this->presenter->user->isAllowed('LeosNeosAssistantsMenu', 'view')) {
                $items["IFMSA Remote"]['List of Incomings'] = 'Ifmsa:incomings';
                $items["IFMSA Remote"]['List of Outgoings'] = 'Ifmsa:outgoings';
            }

            if ($this->presenter->user->isAllowed('RegisteredIncomingsMenu', 'view')) {
                $items["Incomings Management"]['General Information'] = 'Incomings:generalInformation';
                $items["Incomings Management"]['Faculty Information'] = 'Incomings:facultyInformation';
                $items["Incomings Management"]['IFMSA Hierarchy'] = 'Incomings:ifmsaHierarchy';
                $items["Incomings Management"]['List of Incomings'] = 'Incomings:default';
                $items["Incomings Management"]['Login Log'] = 'Incomings:loginLog';
            }

            if ($this->presenter->user->isAllowed('LeosNeosAssistantsMenu', 'view')) {
                $items["Officers Management"]['List of Officers'] = 'Officers:default';
            }

            if ($this->presenter->user->isAllowed('NeosAssistantsMenu', 'view')) {
                $items["Officers Management"]['Default Tasks Manager'] = 'Tasks:defaultTasks';
            }

            if ($this->presenter->user->isAllowed('LeosNeosMenu', 'view')) {
                $items["Officers Management"]['Login Log'] = 'Officers:loginLog';
            }

            if ($this->presenter->user->isAllowed("Showroom", "management")) {
                $items["Showroom Management"]['Showroom'] = 'Showroom:';
                $items["Showroom Management"]['Add CUK Member'] = 'Showroom:officersList';
                $items["Showroom Management"]['Add non-CUK Member'] = 'Showroom:addOfficer';
                $items["Showroom Management"]['Persons in Showroom'] = 'Showroom:list';
            }

            if ($this->presenter->user->isAllowed("Feedback", "management")) {
                $items["Feedback Management"]['Feedback'] = 'Feedback:';
                $items["Feedback Management"]['Visible Countries'] = 'Feedback:countriesManagement';
                $items["Feedback Management"]['List of Feedback'] = 'Feedback:list';
            }

            if ($this->presenter->user->isAllowed('LeosNeosMenu', 'view')) {
                $items["Management"]['List of Faculties'] = 'Faculties:default';
            }

            if ($this->presenter->user->isAllowed('NeosAssistantsMenu', 'view')) {
                $items["Management"]['List of All Events'] = 'Events:list';
                $items["Management"]['List of Transactions'] = 'EventsPayment:transactionList';
            }
        }

        return $items;
    }

    /**
     * Create all menu items with all needed restrictions and information.
     * @return array
     */
    protected function createMenu()
    {
        $items = $this->constructItems();
        $menuItems = array();
        foreach ($items as $key => $item) {
            if (is_array($item)) {
                $active = false;
                $newSubMenu = array();
                foreach ($item as $subKey => $subItem) {
                    $thisActive = false;
                    if (($this->presenter->getName() . ':' . $this->presenter->getView()) == $subItem) {
                        $active = true;
                        $thisActive = true;
                    }
                    $newSubMenu[] = new MenuItem($thisActive, $subKey, $subItem);
                }
                $menuItems[] = new MenuItem($active, $key, "", $newSubMenu);
            } else {
                $menuItems[] = new MenuItem(($this->presenter->getName() . ':' .
                        $this->presenter->getView()) == $item ? true : false, $key, $item);
            }
        }

        return $menuItems;
    }

    /**
     * Render whole menu using internal template.
     */
    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/menu.latte');

        // push some values into template
        $template->menuItems = $this->createMenu();

        // and render it
        $template->render();
    }
}
