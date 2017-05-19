<?php

namespace App\Helpers\Table;

use App\Model\Repository\Events;

/**
 * Factory class for generation of tables which are connected to the events.
 */
class EventsTableFactory
{
    /** @var Events */
    private $events;

    /* EXCEL STYLES */

    private $eventNameExcelStyle;
    private $headerExcelStyle;
    private $paidExcelStyle;
    private $notPaidExcelStyle;

    /**
     * DI Contructor which also creates excel styles.
     * @param Events $events
     */
    public function __construct(
        Events $events
    ) {

        $this->events = $events;

        // setup styles

        $this->eventNameExcelStyle = array(
            'font' => array(
                'italic' => true,
                'size' => 16,
                'name' => 'Arial'
            ));

        $this->headerExcelStyle = array( 'font' => array( 'bold' => true ) );

        $this->paidExcelStyle = array(
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '00FF00')
            ));

        $this->notPaidExcelStyle = array(
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'FF0000')
            ));
    }

    /**
     * Generate table of participants based on given event identification.
     * Table is written directly on the output.
     * @param int $id identification of event
     * @note Response is written to output right in this function
     */
    public function createParticipantsTable($id)
    {
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("CUK System")
                ->setTitle("Participants Table");
        $objPHPExcel->setActiveSheetIndex(0);

        $row = 1;
        $coord = "A" . $row;
        $coordRange = $coord . ":E" . $row;
        $arr = array('Participant Name', 'Email', 'Faculty', 'Country', 'Paid');
        $objPHPExcel->getActiveSheet()->fromArray($arr, null, $coord);
        $objPHPExcel->getActiveSheet()->getStyle($coordRange)->applyFromArray($this->headerExcelStyle);

        $row++;

        $event = $this->events->findOrThrow($id);
        foreach ($event->participants as $inc) {
            $coord = "A" . $row;
            $coordRange = $coord . ":E" . $row;
            $arr = array($inc->user->firstname . ' ' . $inc->user->surname,
                    $inc->user->email, $inc->user->faculty->facultyName,
                    $inc->user->country->countryName, $inc->paid==1 ? "Yes" : "No" );
            $objPHPExcel->getActiveSheet()->fromArray($arr, null, $coord);
            $objPHPExcel->getActiveSheet()->getStyle($coordRange)
                    ->applyFromArray($inc->paid==1?$this->paidExcelStyle:$this->notPaidExcelStyle);

            $row++;
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    /**
     * Generate table of events based on given list of event identifications.
     * Table is written directly on the output.
     * @param array $events array of event ids
     * @note Response is written to output right in this function
     */
    public function createEventsTable(array $events)
    {
        $excel = new \PHPExcel();
        $excel->getProperties()->setCreator("CUK System")
                ->setTitle("Events Table");
        $excel->setActiveSheetIndex(0);

        $row = 1;
        foreach ($events as $id) {
            $event = $this->events->findOrThrow($id);
            $coorganizers = $event->coorganizers;
            $participants = $event->participants;
            if (!$event) {
                continue;
            }

            $coord = "A" . $row;
            $arr = array( $event->eventName );
            $excel->getActiveSheet()->fromArray($arr, null, $coord);
            $excel->getActiveSheet()->getStyle($coord)->applyFromArray($this->eventNameExcelStyle);

            $row++;
            $orgRow = $row;
            $partRow = $row;

            { // organizers columns
                $coord = "C" . $orgRow;
                $coordRange = $coord . ":E" . $orgRow;
                $arr = array( "Organisator", "Coorganisator", "Deadline" );
                $excel->getActiveSheet()->fromArray($arr, null, $coord);
                $excel->getActiveSheet()->getStyle($coordRange)->applyFromArray($this->headerExcelStyle);

                $orgRow++;

                $coord = "C" . $orgRow;
                $arr = array( $event->user->username . " (" . $event->user->email . ")",
                    "", $event->signupDeadline->format("d.m.Y H:i") );
                $excel->getActiveSheet()->fromArray($arr, null, $coord);

            foreach ($coorganizers as $coorg) {
                $coord = "D" . $orgRow;
                $arr = array( $coorg->user->username . " (" . $coorg->user->email . ")" );
                $excel->getActiveSheet()->fromArray($arr, null, $coord);

                $orgRow++;
            }
            if (empty($coorganizers)) {
                $orgRow++;
            }
            }

            { // participants columns
                $coord = "A" . $partRow;
                $coordRange = $coord . ":B" . $partRow;
                $arr = array( "Participant Name", "Paid" );
                $excel->getActiveSheet()->fromArray($arr, null, $coord);
                $excel->getActiveSheet()->getStyle($coordRange)->applyFromArray($this->headerExcelStyle);

                $partRow++;

            foreach ($participants as $part) {
                $coord = "A" . $partRow;
                $coordRange = $coord . ":B" . $partRow;
                $arr = array( $part->user->firstname . " " . $part->user->surname,
                    $part->paid==1?"Yes":"No" );
                $excel->getActiveSheet()->fromArray($arr, null, $coord);
                $excel->getActiveSheet()->getStyle($coordRange)
                    ->applyFromArray($part->paid==1 ? $this->paidExcelStyle : $this->notPaidExcelStyle);

                $partRow++;
            }
            }

            $row = \max(array( $orgRow, $partRow )) + 1;
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save('php://output');
    }
}
