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
        if (strtolower($feedback->exchangeType) == 'scope') {
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
        $pdf->MultiCell($rightColWidth, 0, $feedback->name, 1, '', true);

        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('grade'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->grade, 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('hostCountry'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->country->countryName, 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('hostCity'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->hostCity, 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('hostFaculty'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->hostFaculty, 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('hostDepartment'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->hostDepartment, 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('date'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->startDate->format('j. n. Y') .
                ' - ' . $feedback->endDate->format('j. n. Y'), 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('exchangeType'));
        $pdf->MultiCell($rightColWidth, 0, strtoupper($feedback->exchangeType), 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('preparationVisa'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->preparationVisa ? 'yes' : 'no', 1, '', true);

        $pdf->Cell($leftColWidth, 0, $this->feedbackHelper->getItemDescription('preparationVaccination'));
        $pdf->MultiCell($rightColWidth, 0, $feedback->preparationVaccination ? 'yes' : 'no', 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('preparationComplications'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->preparationComplications, 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('preparationMoney'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->preparationMoney, 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('accommodation'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->accommodation, 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('cpHelp'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->cpHelp, 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('exchangeCommunication'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->exchangeCommunication, 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('socialTravelling'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->socialTravelling, 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('socialProgram'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->socialProgram, 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('furtherTips'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->furtherTips, 1, '', true);

        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $this->feedbackHelper->getItemDescription('overallReview'), 0, '');
        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->MultiCell($pageWidth, 0, $feedback->overallReview, 1, '', true);

        $pdf->Output("feedback_" . $feedback->name . ".pdf");
    }
}
