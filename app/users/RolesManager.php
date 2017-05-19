<?php

namespace App\Users;

/**
 * Roles management which takes care about roles interpretation and description.
 */
class RolesManager
{
    /**
     * Convert given officer role into number.
     * @param string $role
     * @return int
     */
    public function roleToInt($role)
    {
        if ($role == 'admin') {
            $int = 140;
        } elseif ($role == 'treasurer') {
            $int = 130;
        } elseif ($role == 'neo') {
            $int = 120;
        } elseif ($role == 'nore') {
            $int = 115;
        } elseif ($role == 'neo_assist') {
            $int = 110;
        } elseif ($role == 'lore') {
            $int = 105;
        } elseif ($role == 'leo') {
            $int = 100;
        } elseif ($role == 'lore_assist') {
            $int = 85;
        } elseif ($role == 'leo_assist') {
            $int = 80;
        } elseif ($role == 'cp') {
            $int = 60;
        } else {
            $int = 0;
        }

        return $int;
    }

    /**
     * Convert given officer role into human readable description.
     * @param string $role
     * @return string
     */
    public function roleToStr($role)
    {
        if ($role == 'admin') {
            $str = 'Administrator';
        } elseif ($role == 'treasurer') {
            $str = 'Treasurer';
        } elseif ($role == 'neo') {
            $str = "NEO";
        } elseif ($role == 'nore') {
            $str = "NORE";
        } elseif ($role == 'neo_assist') {
            $str = "NEO Assistant";
        } elseif ($role == 'lore') {
            $str = 'LORE';
        } elseif ($role == 'leo') {
            $str = "LEO";
        } elseif ($role == 'lore_assist') {
            $str = 'LORE Assistant';
        } elseif ($role == 'leo_assist') {
            $str = "LEO Assistant";
        } elseif ($role == 'cp') {
            $str = "Contact Person";
        } elseif ($role == 'nobody') {
            $str = "Registered User";
        } else {
            $str = "Unknown";
        }

        return $str;
    }

    /**
     * Convert given incoming role into human readable description.
     * @param string $role
     * @return string
     */
    public function incomingRoleToStr($role)
    {
        if ($role == 'nobody') {
            $str = "Registered User";
        } elseif ($role == 'incoming') {
            $str = "Incoming";
        } else {
            $str = "Unknown";
        }

        return $str;
    }

    /**
     * Get list of possible officer roles.
     * @return array
     */
    public function getRoles()
    {
        $ret = array();
        $ret['nobody'] = 'Registered User';
        $ret['cp'] = 'Contact Person';
        $ret['lore_assist'] = 'LORE Assistant';
        $ret['leo_assist'] = 'LEO Assistant';
        $ret['lore'] = 'LORE';
        $ret['leo'] = 'LEO';
        $ret['neo_assist'] = 'NEO Assistant';
        $ret['nore'] = 'NORE';
        $ret['neo'] = 'NEO';
        $ret['treasurer'] = 'Treasurer';
        //$ret['admin'] = 'Administrator';
        return $ret;
    }

    /**
     * Get list of possible incoming roles.
     * @return array
     */
    public function getIncomingRoles()
    {
        $ret = array();
        $ret['nobody'] = 'Registered User';
        $ret['incoming'] = 'Incoming';
        return $ret;
    }

    /**
     * Get list of officer roles with further description.
     * @return array
     */
    public function getRolesDescription()
    {
        $ret = array();
        $ret['nobody'] = 'Registered User - I can\'t do anything in this system just edit my profile';
        $ret['cp'] = 'Contact Person - I can organize events and see further information about my incomings';
        $ret['lore_assist'] = 'LORE Assistant - I can manage Contact Persons but only from my faculty';
        $ret['leo_assist'] = 'LEO Assistant - I can manage Contact Persons but only from my faculty';
        $ret['lore'] = 'LORE - I can manage Contact Persons but only from my faculty';
        $ret['leo'] = 'LEO - I can manage Contact Persons but only from my faculty';
        $ret['neo_assist'] = 'NEO Assistant - I can do everything user';
        $ret['nore'] = 'NORE - I can do everything user';
        $ret['neo'] = 'NEO - I can do everything user';
        $ret['treasurer'] = 'Treasurer - I can do everything user';
        //$ret['admin'] = 'Administrator';
        return $ret;
    }

    /**
     * Get list of incoming roles with further description.
     * @return array
     */
    public function getIncomingsRolesDescription()
    {
        $ret = array();
        $ret['nobody'] = 'Registered User - I can only edit my profile';
        $ret['incoming'] = 'Incoming - I can see events and further information about them';
        return $ret;
    }
}
