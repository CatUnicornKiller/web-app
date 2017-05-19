<?php

namespace App\Helpers;

use Nette;

/**
 * Helper class for frequent work with nette responses.
 */
class ResponseHelper extends Nette\Object
{
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
     * @param Nette\Http\IResponse $response
     * @param string $filename
     * @return \Nette\Http\IResponse
     */
    public function setCsvFileResponse(Nette\Http\IResponse $response, $filename)
    {
        $response->setContentType('text/plain', 'UTF-8');
        $response->setHeader('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->setHeader('Cache-Control', 'max-age=0');
        return $response;
    }

    /**
     * Set response to XLSX file with given filename.
     * @param Nette\Http\IResponse $response
     * @param string $filename
     * @return \Nette\Http\IResponse
     */
    public function setXlsxFileResponse(Nette\Http\IResponse $response, $filename)
    {
        $response->setContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->setHeader('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->setHeader('Cache-Control', 'max-age=0');
        return $response;
    }
}
