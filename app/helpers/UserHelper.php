<?php

namespace App\Helpers;

use Nette;
use App;

/**
 * Helper for various stuff concerning user management.
 */
class UserHelper
{
    use Nette\SmartObject;

    /**
     * Checks if officer has filled all needed additional information.
     * @param App\Model\Entity\OfficersProfile $officerProfile
     * @return boolean true if additional info is provided by officer
     */
    public function isAdditionalInfoFilled($officerProfile)
    {
        if (strlen(trim($officerProfile->getAddress())) > 0 &&
                strlen(trim($officerProfile->getCity())) > 0 &&
                strlen(trim($officerProfile->getPostCode())) > 0 &&
                strlen(trim($officerProfile->getRegion())) > 0 &&
                strlen(trim($officerProfile->getPhone())) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Checks if request for additional information can be performed or not.
     * @param int $requestsCount number of requests for officer
     * @param App\Model\Entity\OfficersProfile $officerProfile
     * @return boolean true if there are no requests for additional info and
     * officer has not filled all additional info
     */
    public function canRequestAdditionalInfo($requestsCount, $officerProfile)
    {
        if ($requestsCount == 0 &&
                !$this->isAdditionalInfoFilled($officerProfile)) {
            return true;
        }
        return false;
    }
}
