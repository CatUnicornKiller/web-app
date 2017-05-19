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
     * @param type $documentType
     * @param type $facultyName
     * @param type $facultyAddress
     * @param type $defaultFolder
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
