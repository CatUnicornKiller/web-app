<?php

namespace App\Helpers\Pdf;

use App;

/**
 * Factory class which manages generation of feedback PDFs.
 */
class FeedbackPdfFactory
{
    /** @var App\Helpers\FeedbackHelper */
    private $feedbackHelper;

    /**
     * DI Constructor.
     * @param App\Helpers\FeedbackHelper $feedbackHelper
     */
    public function __construct(App\Helpers\FeedbackHelper $feedbackHelper)
    {
        $this->feedbackHelper = $feedbackHelper;
    }

    /**
     * Create PDF with detailed information about feedback.
     * PDF is outputted directly into output.
     * @param App\Model\Entity\Feedback $feedback
     * @param bool $isFancy if true then generate fancier pdf
     */
    public function createFeedbackPdf($feedback, $isFancy)
    {
        // deduce scope or score from datas
        $isScope = false;
        if (strtolower($feedback->getExchangeType()) == 'scope') {
            $isScope = true;
        }

        // *** VARS CREATION ***
        $pdf = new BasePdf('Feedback', '', '', '', './', $isFancy, $isScope);
        $leftColWidth = $pdf->getPageRemainingWidth() / 3;
        $rightColWidth = $pdf->getPageRemainingWidth() / 3 * 2;
        $pageWidth = $pdf->getPageRemainingWidth();

        // *** SETUP ***
        $pdf->SetTitle('Feedback');
        $pdf->SetSubject('Feedback');
        $pdf->AddPage();

        $pdf->SetLineStyle($pdf->getEntryBorderStyle());
        $pdf->SetFillColorArray($pdf->getEntryFillColor());

        // *** DATA ***

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('name'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->getName(), 1, '', true);

        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('grade'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->getGrade(), 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('hostCountry'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->getCountry()->getCountryName(), 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('hostCity'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->getHostCity(), 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('hostFaculty'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->getHostFaculty(), 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('hostDepartment'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->getHostDepartment(), 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('date'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->getStartDate()->format('j. n. Y') .
                ' - ' . $feedback->getEndDate()->format('j. n. Y'), 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('exchangeType'));
        $pdf->MultiCell($rightColWidth, 0, strtoupper($feedback->getExchangeType()), 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('preparationVisa'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->getPreparationVisa() ? 'yes' : 'no', 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('preparationVaccination'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->getPreparationVaccination() ? 'yes' : 'no', 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('preparationComplications'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, strval($feedback->getPreparationComplications()), 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('preparationMoney'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, strval($feedback->getPreparationMoney()), 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('accommodation'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->getAccommodation(), 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('cpHelp'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->getCpHelp(), 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('exchangeCommunication'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->getExchangeCommunication(), 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('socialTravelling'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->getSocialTravelling(), 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('socialProgram'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->getSocialProgram(), 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('furtherTips'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->getFurtherTips(), 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('overallReview'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->getOverallReview(), 1, '', true);

        $pdf->Output("feedback_" . $feedback->getName() . ".pdf");
    }
}
