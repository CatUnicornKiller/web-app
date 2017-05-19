<?php

namespace App\Helpers;

use Nette;

/**
 * Feedback descriptions helper.
 */
class FeedbackHelper extends Nette\Object
{
    /**
     * Items which are used within feedback with its keys and textual values.
     * @var array
     */
    private $items;

    /**
     * Subgroup of items which are suitable for the textareas.
     * @var array
     */
    private $textAnswers;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->items = array(
            'name' => 'Name',
            'grade' => 'Year of Study',
            'hostCountry' => 'Host Country',
            'hostCity' => 'Host City',
            'hostFaculty' => 'Host Faculty',
            'hostDepartment' => 'Host Department',
            'date' => 'Date',
            'startDate' => 'Start Date',
            'endDate' => 'End Date',
            'exchangeType' => 'Exchange type',
            'preparationVisa' => 'Neeeded Visa?',
            'preparationVaccination' => 'Needed vaccination?',
            'preparationComplications' => 'Were there any complications during preparation?',
            'preparationMoney' => 'How much money did you need and for what?',
            'accommodation' => 'Where were you accommodated and what are your experiences with it?',
            'cpHelp' => 'Was your Contact Person helpful?',
            'exchangeCommunication' => 'Did you have any communication problems?',
            'socialTravelling' => 'Where did you travel during your exchange? What are your favourite places there?',
            'socialProgram' => 'Were there any social program planned? How would you review it?',
            'furtherTips' => 'Do you have any tips for other students coming to the same city?',
            'overallReview' => 'What is your experiences with whole exchange, you can mention benefits which exchange brought you, ...?'
            );

        $this->textAnswers = array('preparationComplications',
            'preparationMoney', 'accommodation', 'cpHelp',
            'exchangeCommunication', 'socialTravelling', 'socialProgram',
            'furtherTips', 'overallReview'
            );
    }

    /**
     * Based on given identification return appropriate description.
     * @param string $it
     * @return string
     */
    public function getItemDescription($it)
    {
        return $this->items[$it];
    }

    /**
     * Get array of the longer text fields.
     * @return array
     */
    public function getTextAnswersList()
    {
        return $this->textAnswers;
    }
}
