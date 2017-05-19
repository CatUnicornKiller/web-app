<?php

namespace App\Forms;

use Nette;

/**
 * Class containing factory methods for forms mainly concerning transactions.
 * Alongside factories there can also be success callbacks.
 */
class TransactionsFormsFactory extends Nette\Object
{
    /**
     * Create simple form for the filtering of the transaction list. Returns
     * simple form with GET method set.
     * @param int $year
     * @param int $month
     * @param bool $paid
     * @return \App\Forms\MySimpleForm
     */
    public function createFilterTransactionsListForm($year, $month, $paid)
    {
        $form = new MySimpleForm;
        $form->setMethod('get');

        $yearArr = array();
        for ($i = (intval(date('Y'))); $i >= 2016; $i--) {
            $yearArr[$i] = $i;
        }
        $form->addSelect('year', 'Year', $yearArr);
        $monthArr = array(
            '1' => 'January',
            '2' => 'February',
            '3' => 'March',
            '4' => 'April',
            '5' => 'May',
            '6' => 'June',
            '7' => 'July',
            '8' => 'August',
            '9' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        );
        $form->addSelect('month', 'Month', $monthArr)
                        ->setPrompt('All');
        $form->addCheckbox('paid', "Only paid events");
        $form->addSubmit('send', 'Show results');

        try {
            $form->setDefaults(array(
                'year' => $year,
                'month' => $month,
                'paid' => $paid
            ));
        } catch (\Exception $e) {
        }

        return $form;
    }
}
