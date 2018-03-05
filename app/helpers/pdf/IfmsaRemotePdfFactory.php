<?php

namespace App\Helpers\Pdf;

use App;
use App\Model\Entity\User;
use App\Model\Entity\Faculty;
use App\Users\UserManager;

/**
 * Factory for the PDFs generated based on IfmsaRemote data.
 */
class IfmsaRemotePdfFactory
{
    /** @var User */
    private $user;
    /** @var Faculty */
    private $faculty;
    /** @var App\Helpers\GuzzleFactory */
    private $guzzleFactory;
    /** @var GuzzleHttp\Client */
    private $guzzleClient;
    /** @var App\Users\MyAuthorizator */
    private $myAuthorizator;

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

        $this->user = $userManager->getCurrentUser();
        $this->faculty = $this->user->faculty;
        $this->guzzleFactory = $guzzleFactory;
        $this->guzzleClient = $guzzleFactory->createGuzzleClient();
        $this->myAuthorizator = $myAuthorizator;
    }

    /**
     * Get size of the image on the given URL.
     * @param string $url
     * @return array
     */
    private function getimgsize($url)
    {
        return @getimagesize($url);
        /*$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $file_contents = curl_exec($ch);
        curl_close($ch);

        $new_image = ImageCreateFromString($file_contents);

        $return = array(imagesx($new_image), imagesy($new_image));

        imagedestroy($new_image);

        return $return;*/
    }

    /**
     * Generate ContactPerson PDF with information about some particular
     * incoming given in the form of the $list and $cardOfDocuments.
     * @param array $list
     * @param array $cardOfDocuments
     * @param string $output if non-empty string then PDF will be stored into
     * file on the server
     * @param string $defaultFolder
     */
    public function generateContactPersonPdf($list, $cardOfDocuments, $output = "", $defaultFolder = './')
    {
        $dateOfBirth = date_create_from_format("d/m/Y", $list["dateOfBirth"]);
        $now = new \DateTime();
        $age = $now->diff($dateOfBirth);


        $font = 'dejavusans';
        $style_filled = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(114, 115, 117));
        $first_border_width = 0.5;
        $first_border_style = array('width' => $first_border_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
        $third_border_width = 0.4;
        $third_border_style = array('width' => $third_border_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(73, 22, 29));
        $fifth_border_width = 0.4;
        $fifth_border_style = array('width' => $fifth_border_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(73, 22, 29));
        $left_cell_width = 50;
        $right_cell_width = 75;

        $facultyName = '';
        $facultyAddress = '';
        if ($this->faculty) {
            $facultyName = $this->faculty->facultyName;
            $facultyAddress = $this->faculty->facultyAddress;
        }
        $pdf = new IfmsaRemotePdf(
            'Contact Person',
            $facultyName,
            $facultyAddress,
            $defaultFolder,
            $this->myAuthorizator
        );

        $pdf->SetTitle('Contact Person Info');
        $pdf->SetSubject('Contact Person Info');
        $pdf->AddPage();

        $img_top_pos = PDF_MARGIN_TOP + 6;
        $img_left_pos = PDF_MARGIN_LEFT;
        $imgwidth = 50;
        $imgheight = 0;
        if ($list['jpgPath']) {
            try {
                $response = $this->guzzleClient->head($list["jpgPath"]);
                $mime_img = $response->getHeader('Content-Type');
                $imgsize = $response->getHeader('Content-Length');
                $imginfo = $this->getimgsize($list["jpgPath"]);
                if (($mime_img[0] == "image/gif" || $mime_img[0] == "image/jpeg" || $mime_img[0] == "image/png") &&
                        $imgsize[0] < 4000000 && $imginfo[0] > 0 && $imginfo[1] > 0) {
                    $pdf->Image($list["jpgPath"], $img_left_pos, $img_top_pos, $imgwidth, 0, "", $list["jpgPath"], '', true);
                    $imgheight = ($imgwidth / $imginfo[0]) * $imginfo[1];
                }
            } catch (\Exception $e) {
            }
        }
        $pdf->setPageMark();


        /* First paragraph */
        $pdf->setCellMargins(0, 5, 0, 3);
        $pdf->SetX(PDF_MARGIN_LEFT + $imgwidth + 5);
        $pdf->SetFont($font, 'B');
        $pdf->MultiCell(0, 0, 'Personal Information', 0, '');
        $pdf->SetFont($font);

        $pdf->SetLineStyle($pdf->getEntryBorderStyle());
        $pdf->SetFillColorArray($pdf->getEntryFillColor());

        $pdf->setCellMargins(0, 1, 0, 0);
        $pdf->SetX(PDF_MARGIN_LEFT + $imgwidth + 5);
        $pdf->Cell($left_cell_width, 0, "Name");
        $pdf->MultiCell($right_cell_width, 0, $list["name"] . " " . $list["surname"], 1, "", true);

        $pdf->SetX(PDF_MARGIN_LEFT + $imgwidth + 5);
        $pdf->Cell($left_cell_width, 0, "Exchange is unilateral");
        $pdf->MultiCell($right_cell_width, 0, $list["unilateral"], 1, "", true);

        $pdf->SetX(PDF_MARGIN_LEFT + $imgwidth + 5);
        $pdf->Cell($left_cell_width, 0, "Date of Birth");
        $pdf->MultiCell($right_cell_width, 0, $list["dateOfBirth"] . " (" . $age->y . " years)", 1, "", true);

        $pdf->SetX(PDF_MARGIN_LEFT + $imgwidth + 5);
        $pdf->Cell($left_cell_width, 0, "Nationality");
        $pdf->MultiCell($right_cell_width, 0, $list["nationality"], 1, "", true);

        $pdf->SetX(PDF_MARGIN_LEFT + $imgwidth + 5);
        $pdf->Cell($left_cell_width, 0, "Languages");
        $pdf->MultiCell($right_cell_width, 0, str_replace(';', "\n", $list["languages"]), 1, "", true);

        $pdf->SetX(PDF_MARGIN_LEFT + $imgwidth + 5);
        $pdf->Cell($left_cell_width, 0, "Cell Phone");
        $pdf->MultiCell($right_cell_width, 0, $list["cellular"], 1, "", true);

        $pdf->SetX(PDF_MARGIN_LEFT + $imgwidth + 5);
        $pdf->Cell($left_cell_width, 0, "Email");
        $pdf->MultiCell($right_cell_width, 0, $list["email"], 1, "", true);

        $pdf->setCellMargins(0, 1, 0, 5);
        $pdf->SetX(PDF_MARGIN_LEFT + $imgwidth + 5);
        $pdf->Cell($left_cell_width, 0, "Alternative Email");
        $pdf->MultiCell($right_cell_width, 0, $list["altEmail"], 1, "", true);


        /* Draw border of paragraph */
        $first_border_y = $pdf->GetY();
        if ($first_border_y < $imgheight + $img_top_pos) {
            $first_border_y = $imgheight + $img_top_pos + 5;
        }
        $pdf->SetY($first_border_y);

        $pdf->SetLineStyle($first_border_style);
        $pdf->Line(PDF_MARGIN_LEFT - 5 + ($first_border_width / 2), PDF_MARGIN_TOP + 1, PDF_MARGIN_LEFT - 5 + ($first_border_width / 2), $first_border_y);
        $pdf->Line(PDF_MARGIN_LEFT - 5, PDF_MARGIN_TOP + 1, 210 - PDF_MARGIN_RIGHT + 5, PDF_MARGIN_TOP + 1);
        $pdf->Line(210 - PDF_MARGIN_RIGHT + 5 - ($first_border_width / 2), PDF_MARGIN_TOP + 1, 210 - PDF_MARGIN_RIGHT + 5 - ($first_border_width / 2), $first_border_y);
        $pdf->Line(PDF_MARGIN_LEFT - 5, $first_border_y, 210 - PDF_MARGIN_RIGHT + 5, $first_border_y);


        /* Second paragraph */
        $left_cell_width = 60;
        $right_cell_width = 80;

        $pdf->Rect(
            10,
            $first_border_y + 5,
            190,
            9,
            'DF',
            array('all' => array('width' => 0, 'cap' => 'square', 'join' => 'miter', 'dash' => '0', 'color' =>
                array(199, 183, 183))),
            array(199, 183, 183)
        );
        $pdf->setPageMark();

        $pdf->setCellMargins(0, 5, 0, 2);
        $pdf->setCellPaddings(1, 2, 2, 2);

        if ($this->myAuthorizator->isScope()) {
            $pdf->SetFont($font, 'B');
            $pdf->Cell($left_cell_width, 0, 'DEPARTMENT');
            $pdf->SetFont($font);

            $dep_splitted = explode(";", $list["departmentChosen"]);
            if (count($dep_splitted) == 4) {
                $dep_splitted[1] = substr($dep_splitted[1], 17);
                if ($dep_splitted[1] == false) {
                    $dep_splitted[1] = "";
                }
                $dep_splitted[2] = substr($dep_splitted[2], 15);
                if ($dep_splitted[2] == false) {
                    $dep_splitted[2] = "";
                }
            } elseif (count($dep_splitted) < 4) {
                $dep_splitted = array($list["departmentChosen"], "", "");
            }

            $pdf->setCellMargins(0, 6.9, 0, 4);
            $pdf->setCellPaddings(1, 0, 1, 0);
            $pdf->SetLineStyle($style_filled);
            $pdf->SetFillColor(242, 242, 242, -1);
            $pdf->MultiCell($right_cell_width, 0, $dep_splitted[0], 1, "", true);

            $pdf->setCellMargins(0, 1, 0, 0);
            $pdf->Cell($left_cell_width, 0, "Field Studied");
            $pdf->MultiCell($right_cell_width, 0, $dep_splitted[1], 1, "", true);

            $pdf->Cell($left_cell_width, 0, "Exam Passed");
            $pdf->MultiCell($right_cell_width, 0, $dep_splitted[2], 1, "", true);
        } else {
            $pdf->SetFont($font, 'B');
            $pdf->Cell($left_cell_width, 0, 'PROJECT', 0, 1);
            $pdf->SetFont($font);

            $pdf->setCellMargins(1, 1, 0, 1);
            $pdf->setCellPaddings(1, 0, 1, 0);
            $pdf->SetLineStyle($style_filled);
            $pdf->SetFillColor(242, 242, 242, -1);
            $pdf->MultiCell(210 - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT, 0, $list["departmentChosen"], 1, "", true);
        }

        $pdf->SetLineStyle($style_filled);
        $pdf->setCellMargins(0, 5, 0, 1);
        $pdf->MultiCell($left_cell_width, 0, "Student Remarks", 0, "");
        $pdf->setCellMargins(1, 0, 0, 1);
        $pdf->MultiCell(210 - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT, 0, $list["studentRemarks"], 1, "", true);


        /* Third paragraph */
        $left_cell_width = 70;
        $right_cell_width = 80;
        $second_border_y = $pdf->GetY();

        $pdf->Rect(
            10,
            $second_border_y + 5,
            190,
            9,
            'DF',
            array('all' => array('width' => 0, 'cap' => 'square', 'join' => 'miter', 'dash' => '0', 'color' =>
                array(199, 183, 183))),
            array(199, 183, 183)
        );
        $pdf->setPageMark();

        $pdf->setCellMargins(0, 5, 0, 2);
        $pdf->setCellPaddings(1, 2, 2, 2);

        $pdf->SetFont($font, 'B');
        $pdf->MultiCell(0, 0, 'OTHERS', 0, '');
        $pdf->SetFont($font);

        $pdf->setCellMargins(0, 1, 0, 0);
        $pdf->setCellPaddings(1, 0, 1, 0);
        $pdf->SetLineStyle($style_filled);
        $pdf->SetFillColor(242, 242, 242, -1);

        $pdf->SetFont($font, 'B');
        $pdf->MultiCell(0, 0, 'Emergency Contact', 0, '');
        $pdf->SetFont($font);

        $pdf->Cell($left_cell_width, 0, "Emergency Name");
        $pdf->MultiCell($right_cell_width, 0, $list["emergName"], 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Emergency Telephone Number");
        $pdf->MultiCell($right_cell_width, 0, $list["emergCell"], 1, "", true);

        $pdf->setCellMargins(0, 1, 0, 4);
        $pdf->Cell($left_cell_width, 0, "Emergency Email");
        $pdf->MultiCell($right_cell_width, 0, $list["emergMail"], 1, "", true);

        $pdf->setCellMargins(0, 1, 0, 0);
        $pdf->SetFont($font, 'B');
        $pdf->MultiCell(0, 0, 'Accommodation', 0, '');
        $pdf->setCellMargins(0, 1, 0, 4);
        $pdf->SetFont($font);
        $pdf->MultiCell(210 - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT, 0, $list["accommodation"], 1, "", true);

        $pdf->setCellMargins(0, 1, 0, 0);
        $pdf->SetFont($font, 'B');
        $pdf->MultiCell(0, 0, 'Other Details', 0, '');
        $pdf->setCellMargins(0, 1, 0, 5);
        $pdf->SetFont($font);
        $pdf->MultiCell(210 - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT, 0, $list["otherDetails"], 1, "", true);


        /* Draw border of paragraph */
        $third_border_y = $pdf->GetY();
        $pdf->SetLineStyle($third_border_style);
        $pdf->Line(PDF_MARGIN_LEFT - 5 + ($third_border_width / 2), $second_border_y + 5, PDF_MARGIN_LEFT - 5 + ($third_border_width / 2), $third_border_y);
        $pdf->Line(PDF_MARGIN_LEFT - 5, $second_border_y + 5, 210 - PDF_MARGIN_RIGHT + 5, $second_border_y + 5);
        $pdf->Line(210 - PDF_MARGIN_RIGHT + 5 - ($third_border_width / 2), $second_border_y + 5, 210 - PDF_MARGIN_RIGHT + 5 - ($third_border_width / 2), $third_border_y);
        $pdf->Line(PDF_MARGIN_LEFT - 5, $third_border_y, 210 - PDF_MARGIN_RIGHT + 5, $third_border_y);


        /* Fourth paragraph */
        $pdf->Rect(
            10,
            $third_border_y + 5,
            190,
            9,
            'DF',
            array('all' => array('width' => 0, 'cap' => 'square', 'join' => 'miter', 'dash' => '0', 'color' =>
                array(199, 183, 183))),
            array(199, 183, 183)
        );
        $pdf->setPageMark();

        $pdf->setCellMargins(0, 5, 0, 2);
        $pdf->setCellPaddings(1, 2, 2, 2);

        $pdf->SetFont($font, 'B');
        $pdf->MultiCell(0, 0, 'ARRIVAL DETAILS', 0, '');
        $pdf->SetFont($font);

        $pdf->setCellMargins(0, 1, 0, 0);
        $pdf->setCellPaddings(1, 0, 1, 0);
        $pdf->SetLineStyle($style_filled);
        $pdf->SetFillColor(242, 242, 242, -1);

        $pdf->Cell($left_cell_width, 0, "Arrival Date and Time");
        $pdf->MultiCell($right_cell_width, 0, $list["arrivalDate"], 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Arrival Location");
        $pdf->MultiCell($right_cell_width, 0, $list["arrivalLocation"], 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Arrival Location Details");
        $pdf->MultiCell($right_cell_width, 0, $list["arrivalLocationDetails"], 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Flight/Bus/Train Number");
        $pdf->MultiCell($right_cell_width, 0, $list["flightBusTrainNumber"], 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Departure Date");
        $pdf->MultiCell($right_cell_width, 0, $list["departureDate"], 1, "", true);


        /* Fifth paragraph */
        $left_cell_width = 80;
        $right_cell_width = 100;
        $fourth_border_y = $pdf->GetY();

        if (($fourth_border_y + 5 + 9) >= (297 - PDF_MARGIN_TOP - PDF_MARGIN_BOTTOM)) {
            $pdf->AddPage();
            $fourth_border_y = $pdf->GetY();
        }
        $pdf->Rect(
            10,
            $fourth_border_y + 5,
            190,
            9,
            'DF',
            array('all' => array('width' => 0, 'cap' => 'square', 'join' => 'miter', 'dash' => '0', 'color' =>
                array(199, 183, 183))),
            array(199, 183, 183)
        );
        $pdf->setPageMark();

        $pdf->setCellMargins(0, 5, 0, 2);
        $pdf->setCellPaddings(1, 2, 2, 2);

        $pdf->SetFont($font, 'B');
        $pdf->MultiCell(0, 0, 'Card of documents', 0, '');
        $pdf->SetFont($font);

        $pdf->setCellMargins(0, 1, 0, 0);
        $pdf->setCellPaddings(1, 0, 1, 0);
        $pdf->SetLineStyle($style_filled);
        $pdf->SetFillColor(242, 242, 242, -1);

        foreach ($cardOfDocuments as $key => $val) {
            $doubledot = strpos($key, ":");
            if ($doubledot == false) {
                $pdf->Cell($left_cell_width, 0, $key);
            } else {
                $pdf->Cell($left_cell_width, 0, substr($key, 0, $doubledot));
            }
            $pdf->Cell($right_cell_width, 0, basename($val), 1, 1, "", false, $val);
        }

        $pdf->SetY($pdf->GetY() + 4);

        /* Draw border of paragraph */
        $fifth_border_y = $pdf->GetY();
        $pdf->SetLineStyle($fifth_border_style);
        $pdf->Line(PDF_MARGIN_LEFT - 5 + ($fifth_border_width / 2), $fourth_border_y + 5, PDF_MARGIN_LEFT - 5 + ($fifth_border_width / 2), $fifth_border_y);
        $pdf->Line(PDF_MARGIN_LEFT - 5, $fourth_border_y + 5, 210 - PDF_MARGIN_RIGHT + 5, $fourth_border_y + 5);
        $pdf->Line(210 - PDF_MARGIN_RIGHT + 5 - ($fifth_border_width / 2), $fourth_border_y + 5, 210 - PDF_MARGIN_RIGHT + 5 - ($fifth_border_width / 2), $fifth_border_y);
        $pdf->Line(PDF_MARGIN_LEFT - 5, $fifth_border_y, 210 - PDF_MARGIN_RIGHT + 5, $fifth_border_y);

        if ($output == "") {
            $pdf->Output($list["name"] . "_" . $list["surname"] . "_CP.pdf");
        } else {
            $pdf->Output($output, 'F');
        }
    }

    /**
     * Generate PDF for ThirdParty with information about some particular
     * incoming given in the form of the $list.
     * @param array $list
     * @param string $output if non-empty string then PDF will be stored into
     * file on the server
     */
    public function generateThirdPartyPdf($list, $output = "")
    {
        $dateOfBirth = date_create_from_format("d/m/Y", $list["dateOfBirth"]);
        $now = new \DateTime();
        $age = $now->diff($dateOfBirth);


        $font = 'dejavusans';
        $style_filled = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(114, 115, 117));
        $first_border_width = 0.5;
        $first_border_style = array('width' => $first_border_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
        $second_border_width = 0.4;
        $second_border_style = array('width' => $second_border_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(73, 22, 29));
        $left_cell_width = 50;
        $right_cell_width = 75;


        $facultyName = '';
        $facultyAddress = '';
        if ($this->faculty) {
            $facultyName = $this->faculty->facultyName;
            $facultyAddress = $this->faculty->facultyAddress;
        }
        $pdf = new IfmsaRemotePdf(
            'Third Party',
            $facultyName,
            $facultyAddress,
            './',
            $this->myAuthorizator
        );

        $pdf->SetTitle('Third Party Info');
        $pdf->SetSubject('Third Party Info');
        $pdf->AddPage();


        $img_top_pos = 41.2;
        $img_left_pos = 145;
        $imgwidth = 50;
        $imgheight = 0;
        if ($list['jpgPath']) {
            try {
                $response = $this->guzzleClient->head($list["jpgPath"]);
                $mime_img = $response->getHeader('Content-Type');
                $imgsize = $response->getHeader('Content-Length');
                $imginfo = $this->getimgsize($list["jpgPath"]);
                if (($mime_img[0] == "image/gif" || $mime_img[0] == "image/jpeg" || $mime_img[0] == "image/png") &&
                        $imgsize[0] < 4000000 && $imginfo[0] > 0 && $imginfo[1] > 0) {
                    $pdf->Image($list["jpgPath"], $img_left_pos, $img_top_pos, $imgwidth, 0, "", $list["jpgPath"], '', true);
                    $imgheight = ($imgwidth / $imginfo[0]) * $imginfo[1];
                }
            } catch (\Exception $e) {
            }
        }
        $pdf->setPageMark();


        $pdf->setCellMargins(0, 5, 0, 3);
        $pdf->SetFont($font, 'B');
        $pdf->MultiCell(0, 0, 'Personal Information', 0, '');
        $pdf->SetFont($font);

        $pdf->SetLineStyle($style_filled);
        $pdf->SetFillColor(242, 242, 242, -1);

        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->Cell($left_cell_width, 0, "Name");
        $pdf->MultiCell($right_cell_width, 0, $list["name"] . " " . $list["surname"], 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Date of Birth");
        $pdf->MultiCell($right_cell_width, 0, $list["dateOfBirth"] . " (" . $age->y . " years)", 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Nationality");
        $pdf->MultiCell($right_cell_width, 0, $list["nationality"], 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Arrival Date and Time");
        $pdf->MultiCell($right_cell_width, 0, $list["arrivalDate"], 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Departure Date");
        $pdf->MultiCell($right_cell_width, 0, $list["departureDate"], 1, "", true);

        $pdf->setCellMargins(0, 1, 0, 5);
        $pdf->Cell($left_cell_width, 0, "Contact Person");
        $pdf->MultiCell($right_cell_width, 0, $list["contactPerson"], 1, "", true);

        $first_border_y = $pdf->GetY();
        if ($first_border_y < $imgheight + $img_top_pos) {
            $first_border_y = $imgheight + $img_top_pos + 5;
        }

        $pdf->SetLineStyle($first_border_style);
        $pdf->Line(PDF_MARGIN_LEFT - 5 + ($first_border_width / 2), PDF_MARGIN_TOP + 1, PDF_MARGIN_LEFT - 5 + ($first_border_width / 2), $first_border_y);
        $pdf->Line(PDF_MARGIN_LEFT - 5, PDF_MARGIN_TOP + 1, 210 - PDF_MARGIN_RIGHT + 5, PDF_MARGIN_TOP + 1);
        $pdf->Line(210 - PDF_MARGIN_RIGHT + 5 - ($first_border_width / 2), PDF_MARGIN_TOP + 1, 210 - PDF_MARGIN_RIGHT + 5 - ($first_border_width / 2), $first_border_y);
        $pdf->Line(PDF_MARGIN_LEFT - 5, $first_border_y, 210 - PDF_MARGIN_RIGHT + 5, $first_border_y);

        if ($output == "") {
            $pdf->Output($list["name"] . "_" . $list["surname"] . "_TP.pdf");
        } else {
            $pdf->Output($output, 'F');
        }
    }

    /**
     * Generate PDF for Department with information about some particular
     * incoming given in the form of the $list.
     * @param array $list
     * @param string $output if non-empty string then PDF will be stored into
     * file on the server
     */
    public function generateDepartmentPdf($list, $output = "")
    {
        $dateOfBirth = date_create_from_format("d/m/Y", $list["dateOfBirth"]);
        $now = new \DateTime();
        $age = $now->diff($dateOfBirth);


        $font = 'dejavusans';
        $style_filled = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(114, 115, 117));
        $first_border_width = 0.5;
        $first_border_style = array('width' => $first_border_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
        $second_border_width = 0.4;
        $second_border_style = array('width' => $second_border_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(73, 22, 29));
        $left_cell_width = 50;
        $right_cell_width = 75;

        $facultyName = '';
        $facultyAddress = '';
        if ($this->faculty) {
            $facultyName = $this->faculty->facultyName;
            $facultyAddress = $this->faculty->facultyAddress;
        }
        $pdf = new IfmsaRemotePdf(
            'Department',
            $facultyName,
            $facultyAddress,
            './',
            $this->myAuthorizator
        );

        $pdf->SetTitle('Department Info');
        $pdf->SetSubject('Department Info');
        $pdf->AddPage();


        $img_top_pos = 41.2;
        $img_left_pos = 145;
        $imgwidth = 50;
        $imgheight = 0;
        if ($list['jpgPath']) {
            try {
                $response = $this->guzzleClient->head($list["jpgPath"]);
                $mime_img = $response->getHeader('Content-Type');
                $imgsize = $response->getHeader('Content-Length');
                $imginfo = $this->getimgsize($list["jpgPath"]);
                if (($mime_img[0] == "image/gif" || $mime_img[0] == "image/jpeg" || $mime_img[0] == "image/png") &&
                        $imgsize[0] < 4000000 && $imginfo[0] > 0 && $imginfo[1] > 0) {
                    $pdf->Image($list["jpgPath"], $img_left_pos, $img_top_pos, $imgwidth, 0, "", $list["jpgPath"], '', true);
                    $imgheight = ($imgwidth / $imginfo[0]) * $imginfo[1];
                }
            } catch (\Exception $e) {
            }
        }
        $pdf->setPageMark();


        $pdf->setCellMargins(0, 5, 0, 3);
        $pdf->SetFont($font, 'B');
        $pdf->MultiCell(0, 0, 'Personal Information', 0, '');
        $pdf->SetFont($font);

        $pdf->SetLineStyle($style_filled);
        $pdf->SetFillColor(242, 242, 242, -1);

        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->Cell($left_cell_width, 0, "Name");
        $pdf->MultiCell($right_cell_width, 0, $list["name"] . " " . $list["surname"], 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Date of Birth");
        $pdf->MultiCell($right_cell_width, 0, $list["dateOfBirth"] . " (" . $age->y . " years)", 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Nationality");
        $pdf->MultiCell($right_cell_width, 0, $list["nationality"], 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Languages");
        $pdf->MultiCell($right_cell_width, 0, str_replace(';', "\n", $list["languages"]), 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Cell Phone");
        $pdf->MultiCell($right_cell_width, 0, $list["cellular"], 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Email");
        $pdf->MultiCell($right_cell_width, 0, $list["email"], 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Alternative Email");
        $pdf->MultiCell($right_cell_width, 0, $list["altEmail"], 1, "", true);


        $pdf->setCellMargins(0, 5, 0, 3);
        $pdf->SetFont($font, 'B');
        $pdf->MultiCell(0, 0, 'Education', 0, '');
        $pdf->SetFont($font);

        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->Cell($left_cell_width, 0, "Medical School");
        $pdf->MultiCell($right_cell_width, 0, $list["medSchool"], 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Medical student since");
        $pdf->MultiCell($right_cell_width, 0, $list["medStudentSince"], 1, "", true);

        $pdf->setCellMargins(0, 1, 0, 5);
        $pdf->Cell($left_cell_width, 0, "Clinical Student since");
        $pdf->MultiCell($right_cell_width, 0, $list["clinStudentSince"], 1, "", true);


        /* Draw first border */
        $first_border_y = $pdf->GetY();
        if ($first_border_y < $imgheight + $img_top_pos) {
            $first_border_y = $imgheight + $img_top_pos + 5;
        }
        $pdf->SetY($first_border_y);

        $pdf->SetLineStyle($first_border_style);
        $pdf->Line(PDF_MARGIN_LEFT - 5 + ($first_border_width / 2), PDF_MARGIN_TOP + 1, PDF_MARGIN_LEFT - 5 + ($first_border_width / 2), $first_border_y);
        $pdf->Line(PDF_MARGIN_LEFT - 5, PDF_MARGIN_TOP + 1, 210 - PDF_MARGIN_RIGHT + 5, PDF_MARGIN_TOP + 1);
        $pdf->Line(210 - PDF_MARGIN_RIGHT + 5 - ($first_border_width / 2), PDF_MARGIN_TOP + 1, 210 - PDF_MARGIN_RIGHT + 5 - ($first_border_width / 2), $first_border_y);
        $pdf->Line(PDF_MARGIN_LEFT - 5, $first_border_y, 210 - PDF_MARGIN_RIGHT + 5, $first_border_y);

        $pdf->Rect(
            10,
            $first_border_y + 5,
            190,
            9,
            'DF',
            array('all' => array('width' => 0, 'cap' => 'square', 'join' => 'miter', 'dash' => '0', 'color' =>
                    array(199, 183, 183))),
            array(199, 183, 183)
        );
        $pdf->setPageMark();
        /* End of first border */



        /* DEPARTMENT CHOSEN PARAGRAPH */
        $left_cell_width = 60;
        $right_cell_width = 80;

        $pdf->Rect(
            10,
            $first_border_y + 5,
            190,
            9,
            'DF',
            array('all' => array('width' => 0, 'cap' => 'square', 'join' => 'miter', 'dash' => '0', 'color' =>
                    array(199, 183, 183))),
            array(199, 183, 183)
        );
        $pdf->setPageMark();

        $pdf->setCellMargins(0, 5, 0, 2);
        $pdf->setCellPaddings(1, 2, 2, 2);

        if ($this->myAuthorizator->isScope()) {
            $pdf->SetFont($font, 'B');
            $pdf->Cell($left_cell_width, 0, 'DEPARTMENT');
            $pdf->SetFont($font);

            $dep_splitted = explode(";", $list["departmentChosen"]);
            if (count($dep_splitted) == 4) {
                $dep_splitted[1] = substr($dep_splitted[1], 17);
                if ($dep_splitted[1] == false) {
                    $dep_splitted[1] = "";
                }
                $dep_splitted[2] = substr($dep_splitted[2], 15);
                if ($dep_splitted[2] == false) {
                    $dep_splitted[2] = "";
                }
            } elseif (count($dep_splitted) < 4) {
                $dep_splitted = array($list["departmentChosen"], "", "");
            }

            $pdf->setCellMargins(0, 6.9, 0, 4);
            $pdf->setCellPaddings(1, 0, 1, 0);
            $pdf->SetLineStyle($style_filled);
            $pdf->SetFillColor(242, 242, 242, -1);
            $pdf->MultiCell($right_cell_width, 0, $dep_splitted[0], 1, "", true);

            $pdf->setCellMargins(0, 1, 0, 0);
            $pdf->Cell($left_cell_width, 0, "Field Studied");
            $pdf->MultiCell($right_cell_width, 0, $dep_splitted[1], 1, "", true);

            $pdf->Cell($left_cell_width, 0, "Exam Passed");
            $pdf->MultiCell($right_cell_width, 0, $dep_splitted[2], 1, "", true);
        } else {
            $pdf->SetFont($font, 'B');
            $pdf->Cell($left_cell_width, 0, 'PROJECT', 0, 1);
            $pdf->SetFont($font);

            $pdf->setCellMargins(1, 1, 0, 1);
            $pdf->setCellPaddings(1, 0, 1, 0);
            $pdf->SetLineStyle($style_filled);
            $pdf->SetFillColor(242, 242, 242, -1);
            $pdf->MultiCell(210 - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT, 0, $list["departmentChosen"], 1, "", true);
        }

        $first_border_y = $pdf->GetY();


        $pdf->setCellMargins(0, 5, 0, 2);
        $pdf->setCellPaddings(1, 2, 2, 2);
        $pdf->SetFont($font, 'B');
        $pdf->MultiCell(0, 0, 'EXCHANGE DATES', 0, '');
        $pdf->SetFont($font);

        $pdf->setCellMargins(0, 1, 0, 1);
        $pdf->setCellPaddings(1, 0, 1, 0);
        $pdf->SetLineStyle($style_filled);
        $pdf->SetFillColor(242, 242, 242, -1);

        $pdf->Cell($left_cell_width, 0, "Exchange Start Date");
        $pdf->MultiCell($right_cell_width, 0, $list["exchStartDate"], 1, "", true);

        $pdf->Cell($left_cell_width, 0, "Exchange End Date");
        $pdf->MultiCell($right_cell_width, 0, $list["exchEndDate"], 1, "", true);

        $duration = date_diff(date_create_from_format("d/m/Y", $list["exchStartDate"]), date_create_from_format("d/m/Y", $list["exchEndDate"]));
        $pdf->setCellMargins(0, 1, 0, 5);
        $pdf->Cell($left_cell_width, 0, "Duration");
        $pdf->MultiCell($right_cell_width, 0, $duration->days . " days", 1, "", true);


        /* Draw second border */
        $second_border_y = $pdf->GetY();
        $pdf->SetLineStyle($second_border_style);
        $pdf->Line(PDF_MARGIN_LEFT - 5 + ($second_border_width / 2), $first_border_y + 5, PDF_MARGIN_LEFT - 5 + ($second_border_width / 2), $second_border_y);
        $pdf->Line(PDF_MARGIN_LEFT - 5, $first_border_y + 5, 210 - PDF_MARGIN_RIGHT + 5, $first_border_y + 5);
        $pdf->Line(210 - PDF_MARGIN_RIGHT + 5 - ($second_border_width / 2), $first_border_y + 5, 210 - PDF_MARGIN_RIGHT + 5 - ($second_border_width / 2), $second_border_y);
        $pdf->Line(PDF_MARGIN_LEFT - 5, $second_border_y, 210 - PDF_MARGIN_RIGHT + 5, $second_border_y);
        /* End of second border */


        /* DOCUMENTS */
        $right_cell_width = 170;

        $dep_list[] = $list["department1"];
        $dep_list[] = $list["department2"];
        $dep_list[] = $list["department3"];
        $dep_list[] = $list["department4"];

        $let_list[] = $list["motivationLetter1"];
        $let_list[] = $list["motivationLetter2"];
        $let_list[] = $list["motivationLetter3"];
        $let_list[] = $list["motivationLetter4"];

        $key = array_search($list["departmentChosen"], $dep_list);
        if ($key !== false && $let_list[$key] != "") {
            $mot_letter = $let_list[$key];

            $pdf->setCellMargins(0, 4, 0, 1);
            $pdf->SetLineStyle($style_filled);
            $pdf->MultiCell($left_cell_width, 0, "Motivation Letter " . ($key+1), 0, "");

            $pdf->setCellMargins(1, 0, 0, 1);
            $pdf->SetTextColor(0, 0, 255);
            $pdf->SetFont($font, "U");
            $pdf->Cell($right_cell_width, 0, basename($mot_letter), 1, 1, "", true, $mot_letter);
            $pdf->SetFont($font);
            $pdf->SetTextColor();
        }

        if ($list["languageCertificate"] != "") {
            $pdf->setCellMargins(0, 4, 0, 1);
            $pdf->SetLineStyle($style_filled);
            $pdf->MultiCell($left_cell_width, 0, "Language Certificate", 0, "");

            $pdf->setCellMargins(1, 0, 0, 1);
            $pdf->SetTextColor(0, 0, 255);
            $pdf->SetFont($font, "U");
            $pdf->Cell($right_cell_width, 0, basename($list["languageCertificate"]), 1, 1, "", true, $list["languageCertificate"]);
            $pdf->SetFont($font);
            $pdf->SetTextColor();
        }

        if ($list["hepbAntPath"] != "") {
            $pdf->setCellMargins(0, 4, 0, 1);
            $pdf->SetLineStyle($style_filled);
            $pdf->MultiCell($left_cell_width, 0, "HepB Antibodies count", 0, "");

            $pdf->setCellMargins(1, 0, 0, 1);
            $pdf->SetTextColor(0, 0, 255);
            $pdf->SetFont($font, "U");
            $pdf->Cell($right_cell_width, 0, basename($list["hepbAntPath"]), 1, 1, "", true, $list["hepbAntPath"]);
            $pdf->SetFont($font);
            $pdf->SetTextColor();
        }

        if ($list["tubTestPath"] != "") {
            $pdf->setCellMargins(0, 4, 0, 1);
            $pdf->SetLineStyle($style_filled);
            $pdf->MultiCell($left_cell_width, 0, "Tuberculosis test", 0, "");

            $pdf->setCellMargins(1, 0, 0, 1);
            $pdf->SetTextColor(0, 0, 255);
            $pdf->SetFont($font, "U");
            $pdf->Cell($right_cell_width, 0, basename($list["tubTestPath"]), 1, 1, "", true, $list["tubTestPath"]);
            $pdf->SetFont($font);
            $pdf->SetTextColor();
        }


        if ($output == "") {
            $pdf->Output($list["name"] . "_" . $list["surname"] . "_DEP.pdf");
        } else {
            $pdf->Output($output, 'F');
        }
    }
}
