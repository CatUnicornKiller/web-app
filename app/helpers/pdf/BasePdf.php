<?php

namespace App\Helpers\Pdf;

use TCPDF;

/**
 * Base PDF class extending TCPDF which modifies basic layout and design and
 * fills it with given header and footer information.
 */
class BasePdf extends TCPDF
{
    /** Main identifier of the pdf. */
    private $pageName;
    /** Subname of the pdf. */
    private $pageSubname;
    /** Faculty name. */
    private $facultyName;
    /** Faculty address. */
    private $facultyAddress;
    /** Default folder, used for obtaining images. */
    private $defaultFolder;
    /** Footer image. */
    private $footerImage;
    /** Page width without side margins. */
    private $pageRemainingWidth;
    /** Should pdf be fancy? */
    private $isFancy;
    /** True if pdf is generated for the scope officer. */
    private $isScope;

    /**
     * Constructor.
     * @param string $pageName
     * @param string $pageSubname
     * @param string $facultyName
     * @param string $facultyAddress
     * @param string $defaultFolder
     * @param bool $isFancy
     * @param bool $isScope
     */
    public function __construct(
        $pageName,
        $pageSubname,
        $facultyName,
        $facultyAddress,
        $defaultFolder,
        $isFancy,
        $isScope
    ) {

        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $this->pageName = $pageName;
        $this->pageSubname = $pageSubname;
        $this->facultyName = $facultyName;
        $this->facultyAddress = $facultyAddress;
        $this->defaultFolder = $defaultFolder;
        $this->isFancy = $isFancy;
        $this->isScope = $isScope;
        $this->pageRemainingWidth = $this->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT;

        if (!$isScope) {
            $this->footerImage = $defaultFolder . './images/logo_score_rgb.png';
        } else {
            $this->footerImage = $defaultFolder . './images/bottom_right.png';
        }

        // *** PARENT SETUP ***
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('CatUnicornKiller System');
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $this->SetFont($this->getBaseFont(), '', 12, '', true);
    }

    /**
     * Setup header.
     */
    public function Header()
    {
        $this->Image($this->defaultFolder . './images/top_left.jpg', PDF_MARGIN_LEFT - 5, 4, 0, 21);
        $this->SetFont($this->getBaseFont(), '', 13);
        $this->SetY(10);
        $this->MultiCell(0, 0, $this->pageName, 0, 'C');
        $this->SetCellMargins(0, 2, 0, 0);
        $this->SetFont($this->getBaseFont(), '', 11);
        $this->MultiCell(0, 0, $this->pageSubname, 0, 'C');

        if ($this->isFancy) {
            $this->Rect(
                0,
                PDF_MARGIN_TOP,
                210,
                297 - PDF_MARGIN_TOP,
                'DF',
                array('all' => array('width' => 0, 'cap' => 'square', 'join' => 'miter', 'dash' => '0', 'color' =>
                array(228, 229, 231))),
                array(228, 229, 231)
            );
        } else {
            $this->Line(0, PDF_MARGIN_TOP, 210, PDF_MARGIN_TOP, array('width' => 0.3));
        }
    }

    /**
     * Setup footer.
     */
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        $this->SetFont($this->getBaseFont(), 'B', 10);
        $this->MultiCell(0, 0, $this->facultyName, 0, 'C', false);
        $this->SetFont($this->getBaseFont(), '', 10);
        $this->MultiCell(0, 0, $this->facultyAddress, 0, 'C', false);
        $this->Image($this->footerImage, 150, 279, 45);
    }

    /**
     * Get remaining width of the pdf page without side margins.
     * @return int
     */
    public function getPageRemainingWidth()
    {
        return $this->pageRemainingWidth;
    }

    /**
     * Get basic font.
     * @return string
     */
    public function getBaseFont()
    {
        return 'dejavusans';
    }

    /**
     * Border style.
     * @return array
     */
    public function getEntryBorderStyle()
    {
        return array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(114, 115, 117));
    }

    /**
     * Get RGB coded fill color.
     * @return array
     */
    public function getEntryFillColor()
    {
        if ($this->isFancy) {
            return array(242, 242, 242, -1);
        } else {
            return array(255, 255, 255);
        }
    }
}
