<?php

namespace App\Helpers\Table;

use App;
use App\Model\Repository\CpAssignedAfs;
use App\Model\Repository\IfmsaPersons;
use XLSXWriter;

/**
 * Factory class responsible for generation of tables concerning IfmsaRemote
 * information.
 */
class IfmsaRemoteTableFactory
{
    /** @var IfmsaPersons */
    private $ifmsaPersons;
    /** @var CpAssignedAfs */
    private $assignedAfs;
    /** @var App\Helpers\IfmsaConnectionHelper */
    private $ifmsaConnectionHelper;

    /**
     * DI Constructor.
     * @param IfmsaPersons $ifmsaPersons
     * @param CpAssignedAfs $assignedAfs
     * @param App\Helpers\IfmsaConnectionHelper $ifmsaConnectionHelper
     */
    public function __construct(
        IfmsaPersons $ifmsaPersons,
        CpAssignedAfs $assignedAfs,
        App\Helpers\IfmsaConnectionHelper $ifmsaConnectionHelper
    ) {
        $this->ifmsaPersons = $ifmsaPersons;
        $this->assignedAfs = $assignedAfs;
        $this->ifmsaConnectionHelper = $ifmsaConnectionHelper;
    }

    /**
     * Generate table with the list of incomings or outgoings with some further
     * information based on given AF numbers.
     * @return string xlsx file table content, can be sent as response
     */
    public function createIfmsaRemoteTable(array $afList)
    {
        $data = array(
            array('AF number','Origin NMO','Sex','Student Name','Student Surname','Student email',
                'Department 1','Department 2','Department 3','Department 4','Hospital',
                '1st Desired City','2nd Desired City','3rd Desired City','Exchange Start Date','Exchange End Date',
                'Arrival date and Time','Departure date','Number of flight/train/bus','Accommodation',
                'Contact Person','Contact Person\'s email','Notes')
            );

        //set_time_limit(2 * count($af_list));

        foreach ($afList as $afNumber) {
            if ($afNumber == "") {
                continue;
            }

            $person = $this->ifmsaPersons->findByAfNumber($afNumber);
            $cp = $this->assignedAfs->findOneByAfNumber($afNumber);
            $cpName = '';
            $cpEmail = '';
            if ($cp) {
                $cpName = $cp->getUser()->getFirstname() . ' ' . $cp->getUser()->getSurname();
                $cpEmail = $cp->getUser()->getEmail();
            }

            $personInfo = array();

            $this->ifmsaConnectionHelper->fetchPersonAf($afNumber, $personInfo);
            $this->ifmsaConnectionHelper->fetchPersonCC($person->getConfirmationNumber(), $personInfo);

            $data[] = array($afNumber, $personInfo["originNmo"],
                $personInfo["sex"], $personInfo["name"], $personInfo["surname"],
                $personInfo["email"],
                $personInfo["department1_"][0], $personInfo["department2_"][0],
                $personInfo["department3_"][0], $personInfo["department4_"][0],
                $personInfo["hospital"],
                $personInfo["desiredCity1"], $personInfo["desiredCity2"],
                $personInfo["desiredCity3"],
                $personInfo["exchStartDate"], $personInfo["exchEndDate"],
                $personInfo["arrivalDate"], $personInfo["departureDate"],
                $personInfo["flightBusTrainNumber"], $person->getAccommodation(),
                $cpName, $cpEmail, $personInfo["studentRemarks"]);
        }

        $writer = new XLSXWriter();
        $writer->writeSheet($data);
        return $writer->writeToString();
    }
}
