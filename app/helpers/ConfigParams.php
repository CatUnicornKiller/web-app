<?php

namespace App\Helpers;

use Nette;

/**
 * Parameters of the application given in the configuration file.
 */
class ConfigParams
{
    use Nette\SmartObject;

    /** Default items per page */
    public $itemsPerPage;
    /** Max images per event */
    public $eventImgMaxCount;
    /** Length of generated name for event image */
    public $eventImgNameLength;
    /** Max size of event image */
    public $eventImgFileSize;
    /** Directory of event images */
    public $eventImgDir;
    /** Directory of articles images */
    public $articlesImgDir;
    /** Directory of profile images */
    public $profileImgDir;
    /** Directory of showroom images */
    public $showroomImgDir;

    /**
     * Constructor.
     * @param array $params
     */
    public function __construct($params)
    {
        $this->profileImgDir = $params['profileImgDir'];
        $this->showroomImgDir = $params['showroomImgDir'];
        $this->articlesImgDir = $params['articlesImgDir'];
        $this->itemsPerPage = $params['itemsPerPage'];
        $this->eventImgMaxCount = $params['eventImgMaxCount'];
        $this->eventImgNameLength = $params['eventImgNameLength'];
        $this->eventImgFileSize = $params['eventImgFileSize'];
        $this->eventImgDir = $params['eventImgDir'];
    }
}
