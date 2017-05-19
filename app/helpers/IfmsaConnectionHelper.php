<?php

namespace App\Helpers;

use App;
use GuzzleHttp;
use App\Exceptions\IfmsaConnectionException;
use App\Model\Entity\User;
use App\Model\Entity\OfficersProfile;
use App\Users\UserManager;

/**
 * Holder for some basic person information obtained from ifmsa.org.
 */
class PersonEntry
{
    public $afNumber;
    public $confirmationNumber;
    public $name;
    public $desired;
    public $nationality;
    public $documents;

    /**
     * Holder constructor with all needed data.
     * @param string $afNumber
     * @param string $name
     * @param string $desired
     * @param string $nationality
     * @param string $documents
     * @param string $confirmationNumber
     */
    public function __construct(
        $afNumber,
        $name,
        $desired,
        $nationality,
        $documents = '',
        $confirmationNumber = ''
    ) {

        $this->afNumber = $afNumber;
        $this->name = utf8_decode(str_replace("\x20\xc2\xa0", ' ', $name));
        $this->desired = $desired;
        $this->nationality = utf8_decode($nationality);
        $this->documents = $documents;
        $this->confirmationNumber = $confirmationNumber;
    }

    /**
     * Comparator of person entries which can be used within sort function.
     * @param PersonEntry $a
     * @param PersonEntry $b
     * @return bool
     */
    public static function cmp_PersonEntry($a, $b)
    {
        $first = date_create_from_format("d/m/Y", $a->desired);
        $second = date_create_from_format("d/m/Y", $b->desired);
        return $first > $second;
    }
}

/**
 * On construction computes several information concerning list of incomings or
 * outgoings fetched from ifmsa.org.
 */
class PersonListInfo
{
    /** Desired year. */
    public $year;
    /** Desired month. */
    public $month;
    /** Computed bottom limit of the date which should be fetched. */
    public $bottomLimit;
    /** Computed top limit of the date which should be fetched. */
    public $topLimit;

    /**
     * Computational constructor.
     * @param int $year
     * @param int $month
     */
    public function __construct($year, $month)
    {
        if (!ctype_digit($year)) {
            $year = date('Y');
        }
        if (!ctype_digit($month)) {
            $month = '0';
        }

        if (intval($month) >= 1 && intval($month) <= 12) { // there is particular month given
            $dayTop = cal_days_in_month(CAL_GREGORIAN, $month, $year) - 7;
            $topLimit = new \DateTime($year . "-" . $month . "-" . $dayTop);

            if ($month == 1) {
                $tempYear = $year - 1;
                $tempMonth = 12;
            } else {
                $tempYear = $year;
                $tempMonth = $month - 1;
            }

            $dayBottom = cal_days_in_month(CAL_GREGORIAN, $tempMonth, $tempYear) - 7;
            $bottomLimit = new \DateTime($tempYear . "-" . $tempMonth . "-" . $dayBottom);
        } else { // Option All in months was selected, or bad month given
            $month = '0';
            $topLimit = new \DateTime($year . "-12-24");
            $bottomLimit = new \DateTime(($year - 1) . "-12-24");
        }

        $this->year = $year;
        $this->month = $month;
        $this->bottomLimit = $bottomLimit;
        $this->topLimit = $topLimit;
    }
}

/**
 * Ifmsa.org connection helper which is supposed to fetch requested information
 * from there and take care of login and other stuff.
 * @note This is quite a big piece of code which needs to be refactored.
 */
class IfmsaConnectionHelper
{
    /** @var App\Helpers\GuzzleFactory */
    private $guzzleFactory;
    /** @var GuzzleHttp\Client */
    private $guzzleClient;
    /** @var App\Helpers\MySessionCookieJar */
    private $guzzleMySessionCookieJar;
    /** @var User */
    private $user;
    /** @var OfficersProfile */
    private $userProfile;
    /** @var App\Users\MyAuthorizator */
    private $myAuthorizator;

    /**
     * For normal officers its set to "scope", for NOREs, LOREs etc its set to
     * "score"
     */
    private $targetPage = "scope";
    /** Base for all routes to ifmsa.org */
    private $basePage = "http://exchange.ifmsa.org";

    /**
     * DI Constructor.
     * @param UserManager $userManager
     * @param App\Helpers\GuzzleFactory $guzzleFactory
     * @param App\Users\MyAuthorizator $myAuthorizator
     */
    public function __construct(
        UserManager $userManager,
        App\Helpers\GuzzleFactory $guzzleFactory,
        App\Users\MyAuthorizator $myAuthorizator
    ) {

        $this->guzzleFactory = $guzzleFactory;
        $this->guzzleClient = $guzzleFactory->createGuzzleClient();
        $this->guzzleMySessionCookieJar = $guzzleFactory->createMySessionCookieJar();

        $this->user = $userManager->getCurrentUser();
        $this->userProfile = $this->user->officersProfile;
        $this->myAuthorizator = $myAuthorizator;

        if (!$this->myAuthorizator->isScope()) {
            $this->targetPage = "score";
        }
    }

    /**
     * Some of the fetched texts can be somehow broken, fix them.
     * @param string $str
     * @return string
     */
    private function repairIfmsaString($str)
    {
        $res = preg_replace('!\s+!', ' ', $str);
        if (strlen($res) == 0) {
            return "";
        }
        return trim($res);
    }

    /**
     * Get indexes of all items which can be loaded from ifmsa.org.
     * @return array
     */
    public function initializePersonInfo()
    {
        return array(
            "afNumber",
            "sex",
            "unilateral",
            "dateOfBirth",
            "nationality",
            "languages",
            "medSchool",
            "medStudentSince",
            "clinStudentSince",
            "cellular",
            "email",
            "altEmail",
            "surname",
            "name",
            "department1",
            "department2",
            "department3",
            "department4",
            "exchStartDate",
            "exchEndDate",
            "studentRemarks",
            "desiredCity1",
            "desiredCity2",
            "desiredCity3",
            "originNmo",
            "hospital",
            "arrivalDate",
            "arrivalLocation",
            "flightBusTrainNumber",
            "arrivalLocationDetails",
            "departureDate",
            "otherDetails",
            "emergName",
            "emergMail",
            "emergCell"
        );
    }

    /**
     * Login into ifmsa.org with credentials from officer account.
     * @return string response
     * @throws IfmsaConnectionException in case of connection error
     */
    public function login()
    {
        try {
            $responseCSRF = $this->guzzleClient->get(
                $this->basePage . '/exchange/login',
                [ 'cookies' => $this->guzzleMySessionCookieJar ]
            );
        } catch (\Exception $e) {
            throw new IfmsaConnectionException("Ifmsa.org login error", $e);
        }

        $csrf_token = '';
        $dom = new \DOMDocument;
        $body = $responseCSRF->getBody();
        @$dom->loadHTML($body);

        foreach ($dom->getElementsByTagName('input') as $node) { // load csrf token
            if ($node->getAttribute('name') == "_csrf_token") {
                $csrf_token = $node->getAttribute('value');
                break;
            }
        }

        try {
            $response = $this->guzzleClient->post(
                $this->basePage . '/exchange/login_check',
                [
                    'form_params' => [
                        '_username' => $this->userProfile->ifmsaUsername,
                        '_password' => $this->userProfile->ifmsaPassword,
                        '_csrf_token' => $csrf_token,
                        '_submit' => 'Login',
                    ],
                    'cookies' => $this->guzzleMySessionCookieJar,
                ]
            );
        } catch (\Exception $e) {
            throw new IfmsaConnectionException("Ifmsa.org login error", $e);
        }

        return $response;
    }

    /**
     * Logout from ifmsa.org.
     * @return string response
     */
    public function logout()
    {
        $response = $this->guzzleClient->get(
            $this->basePage . '/exchange/logout',
            [ 'cookies' => $this->guzzleMySessionCookieJar ]
        );
        return $response;
    }

    /**
     * Upload information about given ContactPerson on the ifmsa.org.
     * @param User $user
     * @param OfficersProfile $profile
     * @return boolean true if successful
     * @throws IfmsaConnectionException in case of connection error.
     */
    public function uploadCpInfo($user, $profile)
    {
        $dom = new \DOMDocument;
        for ($i = 0; $i < 2; $i++) {
            // first lets get csrf token
            try {
                $responseCSRF = $this->guzzleClient->get(
                    $this->basePage . '/exchange/' . $this->targetPage . '/explore/contact_persons/add',
                    [ 'cookies' => $this->guzzleMySessionCookieJar ]
                );
            } catch (\Exception $e) {
                throw new IfmsaConnectionException("Something went wrong!", $e);
            }

            $csrf_token = '';
            @$dom->loadHTML($responseCSRF->getBody());
            $csrf_token_node = $dom->getElementById('ez_explorebundle_contactperson__token');
            if ($csrf_token_node !== null) {
                $csrf_token = $csrf_token_node->getAttribute('value');
            }

            // and second lets upload cp info
            try {
                $response = $this->guzzleClient->post(
                    $this->basePage . '/exchange/' . $this->targetPage . '/explore/contact_persons/add',
                    [
                        'form_params' => [
                            'ez_explorebundle_contactperson[firstname]' => $user->firstname,
                            'ez_explorebundle_contactperson[lastname]' => $user->surname,
                            'ez_explorebundle_contactperson[address]' => $profile->address,
                            'ez_explorebundle_contactperson[city]' => $profile->city,
                            'ez_explorebundle_contactperson[postalCode]' => $profile->postCode,
                            'country' => $user->country->countryName,
                            'ez_explorebundle_contactperson[phone]' => $profile->phone,
                            'ez_explorebundle_contactperson[cellular]' => $profile->phone,
                            'ez_explorebundle_contactperson[email]' => $user->email,
                            'lc' => $user->faculty->ifmsaLcNumber,
                            'ez_explorebundle_contactperson[_token]' => $csrf_token
                        ],
                        'cookies' => $this->guzzleMySessionCookieJar
                    ]
                );
            } catch (\Exception $e) {
                throw new IfmsaConnectionException("Something went wrong!", $e);
            }

            $body = $response->getBody();
            @$dom->loadHTML($body);

            foreach ($dom->getElementsByTagName('title') as $title) {
                if (strpos(trim($title->nodeValue), "Redirecting to ") !== false) {
                    return true;
                }
            }

            // if code is here than we have to log into the system
            if ($i == 0) {
                $this->login();
            }
        }

        return false;
    }

    /**
     * Fetch list of outgoings from ifmsa.org and return it as output parameter.
     * @param string $year
     * @param string $bottomLimit
     * @param string $topLimit
     * @param array $personEntryList output param
     * @param array $afList output param
     * @throws IfmsaConnectionException in case of connection error
     */
    public function fetchOutgoings($year, $bottomLimit, $topLimit, & $personEntryList, array & $afList)
    {
        $found = false;
        $dom = new \DOMDocument;
        for ($i = 0; $i < 2; $i++) {
            try {
                $response = $this->guzzleClient->post(
                    $this->basePage . '/exchange/' .
                        $this->targetPage . '/exchange/outgoings/search/1/',
                    [
                        'form_params' => [
                            'lc' => '0',
                            'hostingNmo' => '',
                            'season' => $year . '-' . ($year + 1),
                            'type' => '',
                            'sex' => '',
                            'assigned' => '',
                            'eform' => '',
                            'showAf' => 'all',
                            'tri' => 'all'
                        ],
                        'cookies' => $this->guzzleMySessionCookieJar,
                    ]
                );
            } catch (\Exception $e) {
                throw new IfmsaConnectionException("Something went wrong!", $e);
            }

            $body = $response->getBody();
            @$dom->loadHTML($body);


            foreach ($dom->getElementsByTagName('div') as $node) {
                if (strpos($node->getAttribute('class'), 'row list-line') === false) {
                    continue;
                }

                $found = true;

                $desired = date_create_from_format("d-m-Y", $node->childNodes->item(9)->nodeValue);
                if ($desired === false || $desired < $bottomLimit || $desired > $topLimit) {
                    continue;
                }

                $afList[] = $node->childNodes->item(1)->nodeValue;

                // find confirmation number
                // we have to delete it or some remains can be here
                $confirmationNumber = '';
                foreach ($node->childNodes->item(23)->childNodes as $confNbrEl) {
                    if (!$confNbrEl->hasAttributes()) {
                        continue;
                    }
                    $foundConfirmation = false;

                    foreach ($confNbrEl->attributes as $nameAttr => $valueAttr) {
                        if ($nameAttr != 'href') {
                            continue;
                        }

                        $confStr = 'card_confirmation/';
                        if (strpos($valueAttr->value, $confStr)) {
                            $confirmationNumber = substr(
                                $valueAttr->value,
                                strpos($valueAttr->value, $confStr) + strlen($confStr)
                            );
                            $foundConfirmation = true;
                            break;
                        }
                    }

                    if ($foundConfirmation) {
                        break;
                    }
                }

                $entry = new PersonEntry(
                    $node->childNodes->item(1)->nodeValue,
                    $node->childNodes->item(15)->nodeValue,
                    $desired->format("d/m/Y"),
                    $node->childNodes->item(5)->nodeValue,
                    $node->childNodes->item(25)->childNodes->item(3)->nodeValue,
                    $confirmationNumber
                );

                $personEntryList[] = $entry;
            }

            if ($found == true) {
                break;
            } elseif ($found == false && $i == 1) {
                throw new IfmsaConnectionException("Page cannot be found or user logged in");
            } else {
                $this->login();
            }
        }
    }

    /**
     * Fetch list of incomings from ifmsa.org and return it as output parameter.
     * @param string $year
     * @param string $bottomLimit
     * @param string $topLimit
     * @param array $personEntryList output param
     * @param array $afList output param
     * @throws IfmsaConnectionException in case of connection error
     */
    public function fetchIncomings($year, $bottomLimit, $topLimit, & $personEntryList, array & $afList)
    {
        $found = false;
        $dom = new \DOMDocument;
        for ($i = 0; $i < 2; $i++) {
            try {
                $response = $this->guzzleClient->post(
                    $this->basePage . '/exchange/' .
                        $this->targetPage . '/exchange/incomings/search/1/',
                    [
                    'form_params' => [
                        'sendingNmo' => '',
                        'lc' => '',
                        'season' => $year . '-' . ($year + 1),
                        'type' => '',
                        'sex' => '',
                        'departement1' => '',
                        'eform' => '',
                        'tri' => '',
                        'showAf' => 'all',
                        'requestedIl' => ''
                    ],
                    'cookies' => $this->guzzleMySessionCookieJar,
                    ]
                );
                $body = $response->getBody();
            } catch (\Exception $e) {
                $body = ""; // exchange.ifmsa.org for some reason returns 500
                // on requested address, thus guzzle will throw an exception

                //throw new IfmsaConnectionException("Something went wrong!", $e);
            }

            @$dom->loadHTML($body);
            foreach ($dom->getElementsByTagName('div') as $node) {
                if (strpos($node->getAttribute('class'), 'row list-line') === false) {
                    continue;
                }

                $found = true;

                $desired = date_create_from_format("d-m-Y", $node->childNodes->item(19)->nodeValue);
                if ($desired === false || $desired < $bottomLimit || $desired > $topLimit) {
                    continue;
                }

                $afList[] = $node->childNodes->item(1)->nodeValue;

                // find confirmation number
                // we have to delete it or some remains can be here
                $confirmationNumber = '';
                foreach ($node->childNodes->item(29)->childNodes as $confNbrEl) {
                    if (!$confNbrEl->hasAttributes()) {
                        continue;
                    }
                    $foundConfirmation = false;

                    foreach ($confNbrEl->attributes as $nameAttr => $valueAttr) {
                        if ($nameAttr != 'href') {
                            continue;
                        }

                        $confStr = 'card_confirmation/';
                        if (strpos($valueAttr->value, $confStr)) {
                            $confirmationNumber = substr(
                                $valueAttr->value,
                                strpos($valueAttr->value, $confStr) + strlen($confStr)
                            );
                            $foundConfirmation = true;
                            break;
                        }
                    }

                    if ($foundConfirmation) {
                        break;
                    }
                }

                $entry = new PersonEntry(
                    $node->childNodes->item(1)->nodeValue,
                    $node->childNodes->item(17)->nodeValue,
                    $desired->format("d/m/Y"),
                    $node->childNodes->item(15)->nodeValue,
                    '',
                    $confirmationNumber
                );

                $personEntryList[] = $entry;
            }

            if ($found == true) {
                break;
            } elseif ($found == false && $i == 1) {
                throw new IfmsaConnectionException("Page cannot be found or user logged in");
            } else {
                $this->login();
            }
        }
    }

    /**
     * Fetch ApplicationForm of particular person based on AF number.
     * @param string $afNumber
     * @param array $personInfo output param
     * @return boolean true if successful
     * @throws IfmsaConnectionException in case of connection error
     */
    public function fetchPersonAf($afNumber, & $personInfo)
    {
        //initialize all person_info variables
        $personInfo["afNumber"] = "";
        $personInfo["sex"] = "";
        $personInfo["unilateral"] = "";
        $personInfo["dateOfBirth"] = "";
        $personInfo["dateOfBirth_"] = date_create();
        $personInfo["age_"] = new \DateInterval("PT0S");
        $personInfo["nationality"] = "";
        $personInfo["languages"] = "";
        $personInfo["languages_"] = array();
        $personInfo["medSchool"] = "";
        $personInfo["medStudentSince"] = "";
        $personInfo["clinStudentSince"] = "";
        $personInfo["cellular"] = "";
        $personInfo["email"] = "";
        $personInfo["altEmail"] = "";
        $personInfo["surname"] = "";
        $personInfo["name"] = "";
        $personInfo["department1"] = "";
        $personInfo["department2"] = "";
        $personInfo["department3"] = "";
        $personInfo["department4"] = "";
        $personInfo["department1_"] = array();
        $personInfo["department2_"] = array();
        $personInfo["department3_"] = array();
        $personInfo["department4_"] = array();
        $personInfo["exchStartDate"] = "";
        $personInfo["exchEndDate"] = "";
        $personInfo["studentRemarks"] = "";
        $personInfo["desiredCity1"] = "";
        $personInfo["desiredCity2"] = "";
        $personInfo["desiredCity3"] = "";


        $found = false;
        for ($i = 0; $i < 2; $i++) {
            try {
                $response = $this->guzzleClient->get(
                    $this->basePage . '/exchange/' .
                        $this->targetPage . '/exchange/student/application_form/' . $afNumber,
                    [ 'cookies' => $this->guzzleMySessionCookieJar ]
                );
            } catch (\Exception $e) {
                throw new IfmsaConnectionException("Something went wrong!", $e);
            }
            $body = $response->getBody();
            $dom_view_af = new \DOMDocument;
            @$dom_view_af->loadHTML($body);

            foreach ($dom_view_af->getElementsByTagName('div') as $node) { // load information from Application Form
                if (strpos($node->getAttribute('class'), "col-lg-3") !== false) {
                    if ($node->nodeValue == "AF Number") {
                        $personInfo["afNumber"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "Sex") {
                        $personInfo["sex"] = $this->repairIfmsaString($node->nextSibling->nextSibling->nodeValue);
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "Exchange is unilateral") {
                        $personInfo["unilateral"] = $this->repairIfmsaString($node->nextSibling->nextSibling->nodeValue);
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "Date of birth (dd/mm/yyyy)") {
                        $personInfo["dateOfBirth_"] = date_create_from_format("d/m/Y", $node->nextSibling->nextSibling->nodeValue);
                        $date_now = new \DateTime();
                        $personInfo["age_"] = $date_now->diff($personInfo["dateOfBirth_"]);
                        $personInfo["dateOfBirth"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "Nationality") {
                        $personInfo["nationality"] = $this->repairIfmsaString($node->nextSibling->nextSibling->nodeValue);
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "Language spoken") {
                        $personInfo["languages"] = "";
                        $personInfo["languages_"] = array();
                        $children = $node->nextSibling->nextSibling->childNodes;
                        for ($i = 1; $i < $children->length; $i += 3) {
                            if (($i + 1) >= $children->length) {
                                break;
                            }
                            $personInfo["languages"] .= $children->item($i)->nodeValue .
                                    ' ' . $children->item($i+1)->nodeValue . ';';

                            $personInfo["languages_"][] = $children->item($i)->nodeValue .
                                    ' ' . $children->item($i+1)->nodeValue;
                        }

                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "Medical School") {
                        $personInfo["medSchool"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "# Medical student since") {
                        $personInfo["medStudentSince"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "# Clinical student since") {
                        $personInfo["clinStudentSince"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "Phone Number") {
                        $personInfo["cellular"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "Email") {
                        $personInfo["email"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "Alternative Email") {
                        $personInfo["altEmail"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "Family name") {
                        $personInfo["surname"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "First name") {
                        $personInfo["name"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "1st Desired Department") {
                        $sibling_nodes = $node->nextSibling->nextSibling->childNodes;

                        $personInfo["department1"] = "";
                        $personInfo["department1"] .= $sibling_nodes->item(1)->nodeValue . ';';
                        $personInfo["department1"] .= $sibling_nodes->item(2)->nodeValue . ';';
                        $personInfo["department1"] .= $sibling_nodes->item(5)->nodeValue . ';';

                        $personInfo["department1_"] = array();
                        $personInfo["department1_"][] = $sibling_nodes->item(1)->nodeValue;
                        $personInfo["department1_"][] = $sibling_nodes->item(2)->nodeValue;
                        $personInfo["department1_"][] = $sibling_nodes->item(5)->nodeValue;

                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "2nd Desired Department") {
                        $sibling_nodes = $node->nextSibling->nextSibling->childNodes;

                        $personInfo["department2"] = "";
                        $personInfo["department2"] .= $sibling_nodes->item(1)->nodeValue . ';';
                        $personInfo["department2"] .= $sibling_nodes->item(2)->nodeValue . ';';
                        $personInfo["department2"] .= $sibling_nodes->item(5)->nodeValue . ';';

                        $personInfo["department2_"] = array();
                        $personInfo["department2_"][] = $sibling_nodes->item(1)->nodeValue;
                        $personInfo["department2_"][] = $sibling_nodes->item(2)->nodeValue;
                        $personInfo["department2_"][] = $sibling_nodes->item(5)->nodeValue;

                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "3rd Desired Department") {
                        $sibling_nodes = $node->nextSibling->nextSibling->childNodes;

                        $personInfo["department3"] = "";
                        $personInfo["department3"] .= $sibling_nodes->item(1)->nodeValue . ';';
                        $personInfo["department3"] .= $sibling_nodes->item(2)->nodeValue . ';';
                        $personInfo["department3"] .= $sibling_nodes->item(5)->nodeValue . ';';

                        $personInfo["department3_"] = array();
                        $personInfo["department3_"][] = $sibling_nodes->item(1)->nodeValue;
                        $personInfo["department3_"][] = $sibling_nodes->item(2)->nodeValue;
                        $personInfo["department3_"][] = $sibling_nodes->item(5)->nodeValue;

                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "4rt Desired Department") {
                        $sibling_nodes = $node->nextSibling->nextSibling->childNodes;

                        $personInfo["department4"] = "";
                        $personInfo["department4"] .= $sibling_nodes->item(1)->nodeValue . ';';
                        $personInfo["department4"] .= $sibling_nodes->item(2)->nodeValue . ';';
                        $personInfo["department4"] .= $sibling_nodes->item(5)->nodeValue . ';';

                        $personInfo["department4_"] = array();
                        $personInfo["department4_"][] = $sibling_nodes->item(1)->nodeValue;
                        $personInfo["department4_"][] = $sibling_nodes->item(2)->nodeValue;
                        $personInfo["department4_"][] = $sibling_nodes->item(5)->nodeValue;

                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "1st Desired Project") {
                        $personInfo["department1"] = $node->nextSibling->nextSibling->nodeValue;
                        $personInfo["department1_"][] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "2nd Desired Project") {
                        $personInfo["department2"] = $node->nextSibling->nextSibling->nodeValue;
                        $personInfo["department2_"][] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "3rd Desired Project") {
                        $personInfo["department3"] = $node->nextSibling->nextSibling->nodeValue;
                        $personInfo["department3_"][] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "4rt Desired Project") {
                        $personInfo["department4"] = $node->nextSibling->nextSibling->nodeValue;
                        $personInfo["department4_"][] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "Exchange Start Date (dd/mm/yyyy)") {
                        $personInfo["exchStartDate"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "Exchange End Date (dd/mm/yyyy)") {
                        $personInfo["exchEndDate"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "Space for notes/messages to the Exchange Officer of your Country/Local Committee") {
                        $personInfo["studentRemarks"] = $this->repairIfmsaString($node->nextSibling->nextSibling->nodeValue);
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "1st Desired City") {
                        $personInfo["desiredCity1"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "2nd Desired City") {
                        $personInfo["desiredCity2"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                    if ($node->nodeValue == "3rd Desired City") {
                        $personInfo["desiredCity3"] = $node->nextSibling->nextSibling->nodeValue;
                        $found = true;
                        continue;
                    }
                }
            }

            if ($found == true || $i == 1) {
                break;
            } else {
                $this->login();
            }
        }

        return $found;
    }

    /**
     * Fetch CardOfDocuments of particular person based on AF number.
     * @param string $afNumber
     * @param array $personInfo output param
     * @param array $cardOfDocuments output param
     * @return boolean true if successful
     * @throws IfmsaConnectionException in case of connection error
     */
    public function fetchPersonCard($afNumber, & $personInfo, & $cardOfDocuments)
    {
        // initialize all person_info varibles
        $personInfo["jpgPath"] = "";
        $personInfo["hepbAntPath"] = "";
        $personInfo["tubTestPath"] = "";
        $personInfo["languageCertificate"] = "";
        $personInfo["motivationLetter1"] = "";
        $personInfo["motivationLetter2"] = "";
        $personInfo["motivationLetter3"] = "";
        $personInfo["motivationLetter4"] = "";


        $found = false;
        for ($i = 0; $i < 2; $i++) {
            try {
                $response = $this->guzzleClient->get(
                    $this->basePage . '/exchange/' .
                        $this->targetPage . '/exchange/student/card_of_documents/' . $afNumber,
                    [ 'cookies' => $this->guzzleMySessionCookieJar ]
                );
            } catch (\Exception $e) {
                throw new IfmsaConnectionException("Something went wrong!", $e);
            }
            $body = $response->getBody();
            $dom_view_card = new \DOMDocument;
            @$dom_view_card->loadHTML($body);

            foreach ($dom_view_card->getElementsByTagName('div') as $node) { // load information from Card of Documents
                if ($node->getAttribute('class') == "row") {
                    if ($node->childNodes->length > 1) {
                        $cardOfDocuments[$node->childNodes->item(1)->nodeValue] = "";
                        $found = true;

                        if ($node->childNodes->length > 5) {
                            if ($node->childNodes->item(5)->childNodes->length > 1) {
                                $cardOfDocuments[$node->childNodes->item(1)->nodeValue] =
                                        $this->basePage . $node->childNodes->item(5)->childNodes->item(1)->getAttribute('href');
                            }
                        }
                    }
                }
            }

            if ($found == true || $i == 1) {
                break;
            } else {
                $this->login();
            }
        }

        if ($found == true) { // Get needed values from $card_of_documents
            if (isset($cardOfDocuments["Photo"])) {
                $personInfo["jpgPath"] = $cardOfDocuments["Photo"];
            }
            if (isset($cardOfDocuments["HepB Antibodies count"])) {
                $personInfo["hepbAntPath"] = $cardOfDocuments["HepB Antibodies count"];
            }
            if (isset($cardOfDocuments["Tuberculosis test"])) {
                $personInfo["tubTestPath"] = $cardOfDocuments["Tuberculosis test"];
            }
            if (isset($cardOfDocuments["Language certificate"])) {
                $personInfo["languageCertificate"] = $cardOfDocuments["Language certificate"];
            }
            if (isset($cardOfDocuments["Motivation Letter for Department 1"])) {
                $personInfo["motivationLetter1"] = $cardOfDocuments["Motivation Letter for Department 1"];
            }
            if (isset($cardOfDocuments["Motivation Letter for Department 2"])) {
                $personInfo["motivationLetter2"] = $cardOfDocuments["Motivation Letter for Department 2"];
            }
            if (isset($cardOfDocuments["Motivation Letter for Department 3"])) {
                $personInfo["motivationLetter3"] = $cardOfDocuments["Motivation Letter for Department 3"];
            }
            if (isset($cardOfDocuments["Motivation Letter for Department 4"])) {
                $personInfo["motivationLetter4"] = $cardOfDocuments["Motivation Letter for Department 4"];
            }
        }

        return $found;
    }

    /**
     * Fetch ConfirmationCard of particular person based on AF number.
     * @param string $afNumber
     * @param array $personInfo output param
     * @return boolean true if successful
     * @throws IfmsaConnectionException in case of connection error
     */
    public function fetchPersonCC($afNumber, & $personInfo)
    {
        // initialize all person_info variables
        $personInfo["confirmationNumber"] = $afNumber;
        $personInfo["originNmo"] = "";
        $personInfo["hospital"] = "";
        $personInfo["arrivalDate"] = "";
        $personInfo["arrivalLocation"] = "";
        $personInfo["flightBusTrainNumber"] = "";
        $personInfo["arrivalLocationDetails"] = "";
        $personInfo["departureDate"] = "";
        $personInfo["otherDetails"] = "";
        $personInfo["emergName"] = "";
        $personInfo["emergMail"] = "";
        $personInfo["emergCell"] = "";

        if (!$afNumber || $afNumber == '') {
            return;
        }


        $found = false;
        for ($i = 0; $i < 2; $i++) {
            try {
                $response = $this->guzzleClient->get(
                    $this->basePage . '/exchange/' .
                        $this->targetPage . '/exchange/student/card_confirmation/' . $afNumber,
                    [ 'cookies' => $this->guzzleMySessionCookieJar ]
                );
            } catch (\Exception $e) {
                throw new IfmsaConnectionException("Something went wrong!", $e);
            }
            $body = $response->getBody();
            $dom_view_cc = new \DOMDocument;
            @$dom_view_cc->loadHTML($body);

            foreach ($dom_view_cc->getElementsByTagName('div') as $node) { // load information from Card of Confirmation

                // get emergency contact
                if (strpos($node->getAttribute('class'), 'item-title') !== false &&
                        $node->nodeValue == 'In case of emergency, please contact :') {
                    $emerg_contact = explode('/', $node->nextSibling->nextSibling->nodeValue);

                    if (count($emerg_contact) == 3) {
                        $personInfo["emergName"] = $emerg_contact[0];
                        $personInfo["emergCell"] = $emerg_contact[1];
                        $personInfo["emergMail"] = $emerg_contact[2];
                    }

                    continue;
                }

                if (strpos($node->getAttribute('class'), "col-lg-3") === false) {
                    continue;
                }

                if ($node->nodeValue == "Origin NMO") {
                    $personInfo["originNmo"] = $node->nextSibling->nextSibling->nodeValue;
                    $found = true;
                    continue;
                }
                if ($node->nodeValue == "Accepted in hospital") {
                    $personInfo["hospital"] = $node->nextSibling->nextSibling->nodeValue;
                    $found = true;
                    continue;
                }
                if ($node->nodeValue == "Arrival date and time") {
                    $personInfo["arrivalDate"] = $node->nextSibling->nextSibling->nodeValue;
                    $found = true;
                    continue;
                }
                if ($node->nodeValue == "Arrival Location") {
                    $personInfo["arrivalLocation"] = $node->nextSibling->nextSibling->nodeValue;
                    $found = true;
                    continue;
                }
                if ($node->nodeValue == "Flight/Bus/Train number") {
                    $personInfo["flightBusTrainNumber"] = $node->nextSibling->nextSibling->nodeValue;
                    $found = true;
                    continue;
                }
                if ($node->nodeValue == "Arrival location details") {
                    $personInfo["arrivalLocationDetails"] = $node->nextSibling->nextSibling->nodeValue;
                    $found = true;
                    continue;
                }
                if ($node->nodeValue == "Departure date") {
                    $personInfo["departureDate"] = $node->nextSibling->nextSibling->nodeValue;
                    $found = true;
                    continue;
                }
                if ($node->nodeValue == "Other details") {
                    $personInfo["otherDetails"] = $node->nextSibling->nextSibling->nodeValue;
                    $found = true;
                    continue;
                }
            }

            if ($found == true || $i == 1) {
                break;
            } else {
                $this->login();
            }
        }
    }
}
