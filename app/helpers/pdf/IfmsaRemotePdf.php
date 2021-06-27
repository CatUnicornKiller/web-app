<?php

namespace App\Helpers\Pdf;

use App;

/**
 * Special kind of PDF based on BasePdf for IfmsaRemote stuff with appropriate
 * defaults.
 */
class IfmsaRemotePdf extends BasePdf
{
    /** @var App\Users\MyAuthorizator */
    private $myAuthorizator;

    /**
     * Constructor.
     * @param string $documentType
     * @param string $facultyName
     * @param string $facultyAddress
     * @param string $defaultFolder
     * @param App\Users\MyAuthorizator $myAuthorizator
     */
    public function __construct(
        $documentType,
        $facultyName,
        $facultyAddress,
        $defaultFolder,
        App\Users\MyAuthorizator $myAuthorizator
    ) {

        $this->myAuthorizator = $myAuthorizator;
        if ($this->myAuthorizator->isScope()) {
            $isScope = true;
        } else {
            $isScope = false;
        }

        parent::__construct(
            'Information about the Incoming Exchange Student',
            $documentType,
            $facultyName,
            $facultyAddress,
            $defaultFolder,
            true,
            $isScope
        );
    }
}
