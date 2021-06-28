<?php

namespace App\Helpers;

use Nette;
use Nette\Http\IResponse;

/**
 * Helper class for frequent work with nette responses.
 */
class ResponseHelper
{
    use Nette\SmartObject;

    /** @var Nette\Http\Response */
    private $httpResponse;

    /**
     * DI Constructor.
     * @param Nette\Http\Response $httpResponse
     */
    public function __construct(Nette\Http\Response $httpResponse)
    {
        $this->httpResponse = $httpResponse;
    }

    /**
     * Set response to CSV file with given filename.
     * @param IResponse $response
     * @param string $filename
     * @return IResponse
     */
    public function setCsvFileResponse(IResponse $response, $filename)
    {
        $response->setContentType('text/plain', 'UTF-8');
        $response->setHeader('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->setHeader('Cache-Control', 'max-age=0');
        return $response;
    }

    /**
     * Set response to XLSX file with given filename.
     * @param IResponse $response
     * @param string $filename
     * @return IResponse
     */
    public function setXlsxFileResponse(IResponse $response, $filename)
    {
        $response->setContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->setHeader('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->setHeader('Cache-Control', 'max-age=0');
        return $response;
    }
}
