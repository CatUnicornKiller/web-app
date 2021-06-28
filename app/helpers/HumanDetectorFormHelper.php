<?php

namespace App\Helpers;

use Nette;
use App;

/**
 * Human detector form helper which can be used to add item to form and check
 * if the answer is legit.
 */
class HumanDetectorFormHelper
{
    use Nette\SmartObject;

    /**
     * To given form add check answer textarea.
     * @param App\Forms\MyForm $form
     */
    public function addToForm(App\Forms\MyForm $form)
    {
        $form->addTextArea('checkAnswer', 'How do you call decrease in the amount of red blood cells (RBCs) or hemoglobin in the blood? (hint: one word answer)')
                ->setRequired('Please answer check question.')
                ->setHtmlAttribute('class', 'materialize-textarea');
    }

    /**
     * Check if given string comply check question answer, if not add error to
     * given form.
     * @param App\Forms\MyForm $form
     * @param mixed $values form values
     * @return bool true if given string match, false otherwise
     */
    public function checkForm(App\Forms\MyForm $form, $values)
    {
        if (strtolower($values->checkAnswer) != 'anemia' &&
                strtolower($values->checkAnswer) != 'anaemia') {
            $form->addError('Bad answer to check question.');
            return false;
        }
        return true;
    }
}
